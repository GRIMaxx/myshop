import { log } from '../utils/logger.js';

// Менеджер хранилища
// Гибридное хранение с автоматическим fallback
// Поддержка TTL для автоматического удаления устаревших данных
// Безопасная сериализация с обработкой ошибок
// Миграция данных между версиями
// Оптимизированные проверки доступности хранилищ
// Полная очистка с удалением старых версий
// Логирование всех операций
//----------------------------------------
// Как использовать хранилище:
// Инициализация
// const storage = StorageManager({});
// Установка значений
// storage.set(storage.keys.timeout, Date.now(), true, 86400000); // 24 часа TTL
// storage.set(storage.keys.dismissed, true);
// Получение значений
// const lastShown = storage.get(storage.keys.timeout);
// const isDismissed = storage.get(storage.keys.dismissed);
// Очистка
// storage.clear(storage.keys.timeout);
// storage.clearAll();
// Миграция старых данных
// storage.migrateFromLegacy();
//----------------------------------------
// Рекомендации по использованию:
// Инициализируйте хранилище один раз при запуске приложения
// Для критичных данных используйте persistent: true
// Для временных данных указывайте ttl в миллисекундах
// Вызывайте migrateFromLegacy() при первом запуске новой версии
export const StorageManager = (config = {}) => {
    const PREFIX = '[PWA:STORAGE]';
    const VERSION = 'v1';
    
    // Проверка конфигурации
    if (!config?.app?.name) {
		log(`Не указано имя приложения в конфиге`, 'error', PREFIX);
        throw new Error('Storage configuration error');
    }

    // Состояние доступности хранилищ
    const storageAvailable = {
        local: null,
        session: null
    };

    // Ключи хранилища
    const keys = {
        timeout: `${VERSION}_${config.app.name}_timeout`,
        dismissed: `${VERSION}_${config.app.name}_dismissed`,
        installed: `${VERSION}_${config.app.name}_installed`
    };

    // Проверка доступности хранилища (с кэшированием)
    const isStorageAvailable = (type = 'local') => {
        if (storageAvailable[type] !== null) return storageAvailable[type];
        
        try {
            const testKey = `_${VERSION}_test_`;
            const storage = type === 'local' ? localStorage : sessionStorage;
            storage.setItem(testKey, testKey);
            storage.removeItem(testKey);
            storageAvailable[type] = true;
        } catch (e) {
            storageAvailable[type] = false;
            log(`${type} Storage недоступен:` + e, 'warn', PREFIX);
        }
        return storageAvailable[type];
    };

    // Основной API
    return {
        keys,
        
        /**
        * Установка значения в хранилище
        * @param {string} key - Ключ из storage.keys
        * @param {any} value - Значение для хранения
        * @param {boolean} persistent - Постоянное хранение (localStorage)
        * @param {number|null} ttl - Время жизни в миллисекундах
        * @returns {boolean} Успешность операции
        */
        set(key, value, persistent = true, ttl = null) {
            try {
                const storageData = ttl ? {
                    value,
                    expires: Date.now() + ttl,
                    __metadata: true
                } : value;

                const serialized = JSON.stringify(storageData);
                
                if (persistent && isStorageAvailable('local')) {
                    localStorage.setItem(key, serialized);
                    return true;
                }
                
                if (isStorageAvailable('session')) {
                    sessionStorage.setItem(key, serialized);
                    return true;
                }
                
                return false;
            } catch (e) {
                log(`Ошибка записи:` + e, 'error', PREFIX);
                return false;
            }
        },

        /**
         * Получение значения из хранилища
         * @param {string} key - Ключ из storage.keys
         * @returns {any|null} Сохраненное значение или null
         */
        get(key) {
            try {
                let rawData = null;
                
                if (isStorageAvailable('local')) {
                    rawData = localStorage.getItem(key);
                }
                
                if (!rawData && isStorageAvailable('session')) {
                    rawData = sessionStorage.getItem(key);
                }
                
                if (!rawData) return null;
                
                const data = JSON.parse(rawData);
                
                // Проверка TTL
                if (data?.__metadata && data.expires < Date.now()) {
                    this.clear(key);
                    return null;
                }
                
                return data?.value ?? data;
            } catch (e) {
                log(`Ошибка чтения:` + e, 'error', PREFIX);
                return null;
            }
        },

        /**
         * Удаление значения по ключу
         * @param {string} key - Ключ из storage.keys
         */
        clear(key) {
            try {
                localStorage.removeItem(key);
                sessionStorage.removeItem(key);
            } catch (e) {
                log(`Ошибка очистки:` + e, 'error', PREFIX);
            }
        },

        /**
         * Полная очистка всех данных приложения
         */
        clearAll() {
            try {
                Object.values(this.keys).forEach(key => {
                    localStorage.removeItem(key);
                    sessionStorage.removeItem(key);
                });
                
                // Очистка старых версий
                Object.keys(localStorage)
                    .filter(k => k.startsWith(`${VERSION}_${config.app.name}`))
                    .forEach(k => localStorage.removeItem(k));
                
                Object.keys(sessionStorage)
                    .filter(k => k.startsWith(`${VERSION}_${config.app.name}`))
                    .forEach(k => sessionStorage.removeItem(k));
            } catch (e) {
                console.error(`${PREFIX} Ошибка полной очистки:`, e);
            }
        },

        /**
         * Миграция данных со старых версий
         */
        migrateFromLegacy() {
            const legacyPrefixes = ['v0_', 'old_', config.app.name.toLowerCase()];
            
            legacyPrefixes.forEach(prefix => {
                Object.keys(localStorage)
                    .filter(k => k.startsWith(prefix))
                    .forEach(oldKey => {
                        const newKey = `${VERSION}_${oldKey.replace(prefix, '')}`;
                        const value = localStorage.getItem(oldKey);
                        this.set(newKey, value);
                        localStorage.removeItem(oldKey);
                    });
            });
        }
    };
};