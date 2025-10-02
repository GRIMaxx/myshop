/**
	БД - Память в браузере - GX-29.05.2025
**/
import { getValidPWAConfigServer } from '../config/default_config_pwa.js'; 
import { log } from '../utils/logger.js';

const CONFIG = getValidPWAConfigServer();					// Получитьактуальные настройки
const px = '[PWA:IDB]';										// Установим префикс для логов

export const IDB = {
	_dbInstances: {},

	// Инициализация
	// Основное предназначение:
	// Подключение к IndexedDB Открывает/создает БД  и хранилище по ключу storeKey из конфига.
	// Инициализация структуры
	//  Создает хранилище (если не существует) с указанными индексами.
	// Миграции данных
	//  Обновляет схему данных при изменении версий.
	// Возвращает готовый экземпляр БД
	//  Дает доступ к хранилищу для операций (get/set/delete).
    // Где используется:
    // Перед любыми операциями с хранилищем (get/set), чтобы гарантировать, что БД готова к работе.
	// Как использовать: await IDB.init('sw_config');
	async init(storeKey) {

        // Без флага нет возможности получить данные.
		if (!CONFIG.allowedStorages[storeKey]) {
			log(`Неверный флаг хранилища`, 'error', px);
			return null;
		}	
		
		// Если данные существуют отдаем их (Это хранилище в рамках запроса)		
		if (this._dbInstances[storeKey]) {
			log(`Используем уже открытое подключение для ${storeKey}`, null, px);
			return this._dbInstances[storeKey];
		}
		
		// Получить по флагу:
		const { 
			dbName,			// Названия БД 
			storeName,  	// Названия хранилища
			indexes,		// 
			storeVersion    // Версия хранилища (отвечает только за миграции данных внутри хранилищ.)
		} = CONFIG.allowedStorages[storeKey];
		
		// Получаем версию БД (отвечает только за структуру БД (хранилища/индексы).)
		const dbVersion = CONFIG.db[dbName]?.version;
		
		return new Promise((resolve, reject) => {
		
		    // 1. Подключение к IndexedDB 
			// 1.1 - Открывает/создает БД (Браузере --> Application --> dbName)
			// Если база данных с таким именем не существует, она будет создана.
			// Или если существует и браузер обнаружил, что новая dbVersion > текущей версии БД,
			// то сразу сработает событие request.onupgradeneeded = (event) => {}
			const request = indexedDB.open(
				dbName,
				// Версия базы данных.
				// Как работает версия IndexedDB при открытии:
				// При вызове indexedDB.open(dbName, dbVersion) браузер сравнивает указанную версию 
				// с текущей версией базы в браузере.
				// Если dbVersion больше текущей версии, вызывается onupgradeneeded.
				// Если dbVersion равна или меньше текущей — onupgradeneeded не вызывается.
				dbVersion
			);
			
			// Если версия базы данных изменилась или если бд нет еще в хранилище  
			request.onupgradeneeded = (event) => {
				const db = event.target.result;										// Получить Бд в которой событие
				const oldVersion = event.oldVersion;               					// Текущая версия БД (0 если новая)
				const newVersion = event.newVersion || db.version || dbVersion;		// Новая
				
				log(`Старт --> Обновление версии IndexedDB с ${oldVersion} до ${newVersion} для ${dbName}`, null, px);

				// 1. Создаем хранилище, если его нет (для новой БД или нового storeKey)
				// (Браузере -> Application --> dbName --> storeName
				if (!db.objectStoreNames.contains(storeName)) {
					
					const store = db.createObjectStore(
						storeName, 
						{ keyPath: 'key' }
					);
					
					// Добавляем индексы из конфига
					if (indexes) {
						// Создаем индекс для быстрого поиска 
						indexes.forEach(index => {
							try {
								// Создаем индекс для быстрого поиска по timestamp
								store.createIndex(
									index.name, 
									index.keyPath, 
									index.options
								);
								log(`Создано новое хранилище: ${storeName} в БД: ${dbName}`, null, px);	
							} catch (e) {
								log(`Ошибка создания индекса ${index.name}: ${e}`, 'error', px);
							}
						});
					}
				}

				// 2. Если версия БД повысилась (не первая установка)
				// Здесь только структурные изменения БД (например, новые хранилища для других storeKey)
				if (oldVersion > 0 && oldVersion < newVersion) {
					
					// Сначала находим все хранилища из конфига, которые относятся к текущей БД (dbName)
					const storesForThisDB = Object.values(CONFIG.allowedStorages)
					// Откидываем текущее так как оно ранее было создано
					.filter(store => store.dbName === dbName);
					
					// 2. Создаем недостающие хранилища
					storesForThisDB.forEach(storeConfig => {
						if (!db.objectStoreNames.contains(storeConfig.storeName)) {
							const store = db.createObjectStore(storeConfig.storeName, { keyPath: 'key' });
							log(`Создаем явно недостающие хранилища: ${storeConfig.storeName}`, null, px);
							// 3. Добавляем индексы
							storeConfig.indexes?.forEach(index => {
								try {
									store.createIndex(index.name, index.keyPath, index.options);
								} catch (e) {
									log(`Ошибка создания индекса ${index.name}: ${e}`, 'error', px);
								}
							});
						}
					});
				}
			};
			
			request.onsuccess = async (event) => {
				const db = event.target.result;
				
				// Сохраняем подключение к БД в this._dbInstances[storeKey] для повторного использования
				this._dbInstances[storeKey] = db;

				try {

					// Вызываем checkAndUpdateData() для проверки/обновления данных хранилища
					await this.checkAndUpdateData(storeKey, db);
						
				} catch (e) {
					log(`Ошибка при обновлении данных: ${e}`, 'error', px);
					// Не reject-им здесь, только логируем
				}

				log(`IndexedDB открыта: ${dbName}`, null, px);
				
				// В случае успеха - резолвим промис с объектом БД
				resolve(db);
			};
				
			request.onerror = (event) => {
				log(`Ошибка открытия IndexedDB: ${event.target.error}`, 'error', px);
				// Реджектим промис с ошибкой
				reject(event.target.error);
			};

			request.onblocked = () => {
				// Логируем предупреждение о блокировке (если БД уже открыта в другой вкладке)
				log(`Открытие IndexedDB заблокировано (другая вкладка использует БД)`, 'warn', px);
			};
		});
	},
	
	// Автоматическое удаление устаревших данных по `timestamp` для IndexedDB
	// Чистка происходит только после успешного обновления версии если даных нет или данные актуальны чистки не будет
	// Если нужно постоянно проверять и если есть удалять то нужно так подкл. await IDB.purgeExpiredData('sw_config', db); 
	async purgeExpiredData(storeKey, db) {
		if (!db) return Promise.reject('DB not connected');
		const storeConfig = CONFIG.allowedStorages[storeKey];
		if (!storeConfig?.maxAge) return;

		const { storeName, maxAge } = storeConfig;
		const cutoffTime = Date.now() - maxAge * 1000;

		return new Promise((resolve, reject) => {
			const tx = db.transaction(storeName, 'readwrite');
			const store = tx.objectStore(storeName);
			const range = IDBKeyRange.upperBound(cutoffTime);

            // Проверка существования индекса timestamp_idx
			if (!store.indexNames.contains('timestamp_idx')) {
				log(`Индекс timestamp_idx отсутствует в ${storeKey}`, 'warn', px);
				return resolve(false);
			}
			
			const req = store.index('timestamp_idx').openCursor(range);

			let deletedCount = 0;
			req.onsuccess = (e) => {
				const cursor = e.target.result;
				if (cursor) {
					cursor.delete();
					deletedCount++;
					cursor.continue();
				} else {
					if (deletedCount > 0) {
						log(`Удалено ${deletedCount} устаревших записей в ${storeKey}`, 'info', px);
					}
					resolve(true);
				}
			};
			req.onerror = (e) => reject(e.target.error);
		});
	},
	
	// Проверка и обновление данных в хранилище после открытия БД. 
	// Пример хранилище: Браузер --> Application --> Имя БД --> Имя хранилища
	// Можно, например, хранить версию данных в отдельном ключе и обновлять данные, если версия изменилась.
	// storeKey -	Флаг для определения какие данные применить   test --> sw_config
	// db       -  Данные открытой уже БД                        test --> PWA_ConfigDB  
	async checkAndUpdateData(storeKey, db) {
		const storeName = CONFIG.allowedStorages[storeKey].storeName;
		return new Promise((resolve, reject) => {
				
			// Создают транзакцию на запись ('readwrite') по заданному storeName в базе db.
			// Получают из этого хранилища storeName запись с ключом '__data_version__' -  
			// условный ключ, под которым обычно хранится текущая версия данных.
			const tx = db.transaction(storeName, 'readwrite');
			const store = tx.objectStore(storeName);
			const versionKey = '__data_version__'; 						// ключ для версии данных
			const getVersionReq = store.get(versionKey);
			
			getVersionReq.onsuccess = () => {
					
				// Эта часть кода получает ранее сохранённую версию данных, хранящуюся под 
				// ключом '__data_version__' в object store, и возвращает её (или 0, если её нет).
				const currentVersion = getVersionReq.result && getVersionReq.result.value !== undefined
				? getVersionReq.result.value : 0;

				// Получить последнюю версию хранилища 
				const expectedVersion = CONFIG.allowedStorages[storeKey]?.storeVersion || 1;
					
				// Сверяем версии если, если есть новая версия хранилища переходим к обновлению
				if (currentVersion < expectedVersion) {
						
					log(`Обновление данных с версии ${currentVersion} до ${expectedVersion} для ${storeKey}`, null, px);

					//-- Удаление старой версии Если она была 
					const deleteOldVersionReq = store.delete(versionKey);
						
					deleteOldVersionReq.onsuccess = () => {
						const putReq = store.put({ 
							key: versionKey, 
							value: expectedVersion, 
							timestamp: Date.now() 
						});
							
						putReq.onsuccess = async () => {
							// Автоматическое удаление устаревших данных если они есть.
							await this.purgeExpiredData(storeKey, db);
							log(`Версия данных обновлена до ${expectedVersion} для ${storeKey}`, null, px);
							resolve(true);
						};

						putReq.onerror = (e) => reject(e.target.error);
					};
						
					deleteOldVersionReq.onerror = (e) => {
						log(`Ошибка удаления старой версии: ${e.target.error}`, 'error', px);
						reject(e.target.error);
					};
						
				} else {
					// Данные актуальны, ничего не меняем
					resolve(true);
				}
			}
			getVersionReq.onerror = (e) => {reject(e.target.error);};				
		});
	},

	// Сохранить данные
	// Пример вызова
	// await IDB.saveData('sw_config', 'user_settings', { theme: 'dark' }); 
	async saveData(storeKey, key, value) {
		try {
			const db = await this.init(storeKey);
			if (!db) return false;

			const { storeName, maxAge } = CONFIG.allowedStorages[storeKey];
			const tx = db.transaction(storeName, 'readwrite');
			const store = tx.objectStore(storeName);

			let stringifiedValue;
			try {
				stringifiedValue = typeof value === 'object'
					? JSON.stringify(value)
					: value;
			} catch (e) {
				log(`Ошибка сериализации данных для ключа ${key}: ${e}`, 'error', px);
				return false;
			}

			await store.put({
				key,
				value: stringifiedValue,
				timestamp: Date.now(),
				expiresAt: maxAge ? Date.now() + maxAge * 1000 : null
			});

			log(`Данные сохранены. Ключ: ${key} в ${storeKey}`, null, px);
			return true;
		} catch (e) {
			log(`Ошибка сохранения данных: ${e}`, 'error', px);
			return false;
		}
	},

	// Получить данные из хранилища
	// storeKey - Флаг по нему определяем имя БД
	// key      - Ключ раздела данных 
	// const data = await IDB.getData('sw_config', 'user_settings');
	async getData(storeKey, key) {
		try {
			const db = await this.init(storeKey);
			if (!db) return null;

			const storeName = CONFIG.allowedStorages[storeKey].storeName;
			const tx = db.transaction(storeName, 'readonly');
			const store = tx.objectStore(storeName);
			const result = await new Promise((resolve, reject) => {
				const req = store.get(key);
				req.onsuccess = () => resolve(req.result);
				req.onerror = (e) => reject(e.target.error);
			});

			if (!result) return null;
		
			// Пытаемся парсить только строки, которые могут быть JSON
			if (typeof result.value === 'string') {
				try {
					return JSON.parse(result.value);
				} catch {
					log(`Данные не в JSON-формате для ключа ${key}`, 'warn', px);
				}
			}
			return result.value;
		} catch (e) {
			log(`Ошибка получения данных: ${e}`, 'error', px);
			return null;
		}
	},
	
	// Удалить запись
	// await IDB.deleteData('sw_config', 'old_data');
	async deleteData(storeKey, key) {
		try {
			const db = await this.init(storeKey);
			if (!db) return false;

			const storeName = CONFIG.allowedStorages[storeKey].storeName;
			const tx = db.transaction(storeName, 'readwrite');
			const store = tx.objectStore(storeName);
			
			await new Promise((resolve, reject) => {
				const req = store.delete(key);
				req.onsuccess = () => resolve(true);
				req.onerror = (e) => reject(e.target.error);
			});

			log(`Данные с ключом ${key} удалены из ${storeKey}`, null, px);
			return true;
		} catch (e) {
			log(`Ошибка удаления данных: ${e}`, 'error', px);
			return false;
		}
	},

	// Очистить хранилище
	// await IDB.clearStore('offline');
	async clearStore(storeKey) {
		try {
			const db = await this.init(storeKey);
			if (!db) return false;

			const storeName = CONFIG.allowedStorages[storeKey].storeName;
			const tx = db.transaction(storeName, 'readwrite');
			const store = tx.objectStore(storeName);
			
			await new Promise((resolve, reject) => {
				const req = store.clear();
				req.onsuccess = () => resolve(true);
				req.onerror = (e) => reject(e.target.error);
			});

			log(`Все данные очищены из ${storeKey}`, null, px);
			return true;
		} catch (e) {
			log(`Ошибка очистки хранилища: ${e}`, 'error', px);
			return false;
		}
	},

	// Получить все ключи
	// const keys = await IDB.getAllKeys('cache_grouped');
	async getAllKeys(storeKey) {
		try {
			const db = await this.init(storeKey);
			if (!db) return [];

			const storeName = CONFIG.allowedStorages[storeKey].storeName;
			const tx = db.transaction(storeName, 'readonly');
			const store = tx.objectStore(storeName);
			
			return await new Promise((resolve, reject) => {
				const req = store.getAllKeys();
				req.onsuccess = () => resolve(req.result || []);
				req.onerror = (e) => reject(e.target.error);
			});
		} catch (e) {
			log(`Ошибка получения ключей: ${e}`, 'error', px);
			return [];
		}
	},

	// Закрыть соединение с БД
	// Пример использования: IDB.close('sw_config');
    // Когда реально нужно закрывать IndexedDB вручную
	// Перед выгрузкой страницы
	// При смене пользователя / сессии / окружения
	// Перед удалением / пересозданием базы данных вручную
	close(storeKey) {
		if (this._dbInstances[storeKey]) {
			this._dbInstances[storeKey].close();
			delete this._dbInstances[storeKey];
			log(`Закрыто подключение к ${storeKey}`, 'info', px);
		}
	}
}; 
