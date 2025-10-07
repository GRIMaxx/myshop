# MyShop Project servicegxx@gmail.com

This is my personal marketplace project and a demonstration of my skills in working with Laravel 12, PHP, and other technologies.

I am planning a marketplace for 10,000,000+ products, and for such a large number of products, I need the most efficient and very fast product search in the search bar.
In this day and age, people have become very lazy, and the overload of advertising, outdated product lists, and so on have prompted me to create my own search engine.

I want customers to find what they are looking for 100% of the time from the first search, and most importantly, I want them to remain in a good mood while searching so that they will buy what they are looking for.
I took the best from my analysis of global websites...

And so, the main focus is on reactive search and optimization for maximum speed imaginable.

But there is a problem: since the code was copied more than 30 times in just one day, I will not be updating it publicly after October 4, 2025.

Guys, this is my experience and hard work, and it's very difficult, so I added a private repository where I will update my code and project every day as much as possible.  
Therefore, “employers” or people who are interested in the project and my ideas, I am happy to discuss joint work with you and welcome any help.

 I am currently looking for a job and am open to offers.

Thank you for your understanding!


## About the project
- Uses HTML/CSS3/SCSS/Bootstrap5/JS/React/Laravel 12, PHP 8, Redis, ...
- Responsive design
- CRUD operations
- 4 levels of data storage (for maximum optimization and super speed - 100+ tests were conducted)
- 3 levels of backend cache for super-fast data retrieval
- All core mechanisms run  on their own providers for scalability + flexibility, etc.
## Other

Guys, I am creating this project myself, and I am the only one adding dynamics to the template. 60% of the front end is taken from another author's HTML layout.
I may sometimes upload less code and sometimes more, as I said, only to the private repository. 
It all depends on my free time, but I try to work with the code for about 7+ hours during the day and 5+ hours at night. 

# Сборка в приватной ветке +

08.10.2025 - сборка (htnl/css/React/...) - Итог на 99% собран какркас поля ввода + уже работает динамика:
- При вводе текста теперь можно удалить при клике на ярлык крестик 
- Добавлен выпадающий список вполе поиск категории как у (https://www.amazon.com/ и https://www.ebay.com/) 
- Добавлен выпадающий список остальных фильтров в поле поиск +- их будет до 23 
- Проведены тесты на кроссбраузерность и кроссплатформенность но пока только от 991 до 1560 ширина (менее 991 буду собирать позже там есть нюансы) 
- React + (@tanstack/react-query)
- Добавлена централизированная передача даных на всю глубину дерева (тоесть props можно не передавать как параметры-аргументы а гораздо удобнее...)
- Добавлен механизм axios (конфиг + централизированая получения и передача даных теперь при любом запросе и ответе мы получаем 100% гарантии что все ок или не ок)
- При вводе текста в поиск:
    - DebouncedQuery задержка 400 мс (можно изменить) - если ползователь остановил ввод только после отправить запрос по истечении 400мс тоесть отправка не посимвольно а при остановке ввода
    - Если запрос отправлен но пользователь заново начал ввод превидущиц запрос отменяем (таким образом нет ошибки игонки!)
    - staleTime: - 30 секунд кэш - данные берутся из кэша, запрос не идёт на сервер после : При следующем вызове Query — рефетч с сервера
    - cacheTime: - 5 мин — держим кэш в памяти - это время жизни кэша в памяти после того, как на него больше никто не ссылается. После cacheTime React Query удаляет данные из памяти — чтобы не накапливать мусор и не           держать лишние запросы в RAM.
    - refetchOnWindowFocus - тобы не дёргало сервер, когда пользователь просто переключился на вкладку
    - refetchOnReconnect - Чтобы не триггерить повторный запрос при восстановлении соединения
    - retry: 1 - Количество повторных попыток при ошибке запроса (по умолчанию — 3) максимум 1 повтор при ошибке
    -  useQuery --> подписан на debouncedQuery --> запрос выполняется только после задержки, а не посимвольно. 
    - const { data = [], isFetching, isError } = useQuery({...}) интересная штука протестил просто супер 
    короче пакет @tanstack/react-query избавляет от ручной обработки от кеша до получения даных  isFetching, isError все что нужно 
    ошибка или идет процес все остальное берет пакет класно же.
Вобщем и много других примочек остановился пока на запросе к серверу нужно там допилит тестовый ответ!
    






