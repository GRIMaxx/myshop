// Если получиться так что все до единого нужны то проше подключить одной строкой
// import * as bootstrap from 'bootstrap'; а все остальные удалить!

// -- Как правило import достаточно для *.blade.php --> HTML код увидет
// -- Но в <script>... Здесь импорт не виден и в кастомных .\resources\js\customs\components\*
// -- тоже иморта не видно !

import '@popperjs/core';
import Popover from 'bootstrap/js/dist/popover';
import Modal from 'bootstrap/js/dist/modal';
import Dropdown from 'bootstrap/js/dist/dropdown';
import Tooltip from 'bootstrap/js/dist/tooltip';
import Collapse from 'bootstrap/js/dist/collapse';
import Button from 'bootstrap/js/dist/button';
import Tab from 'bootstrap/js/dist/tab';

// -- По этому принято решения зделать глабально но раздробить и подкл. только те
// -- которые будут использоваться
// -- Как подключать в (Кастомных файлах) - теперь все в window.bootstrap.* и те модули которые здесь подключены все!

if (typeof window !== 'undefined') {
    window.bootstrap = {
        Dropdown,
        Modal,
        Popover,
        Tooltip,
        Collapse,
        Button,
        Tab
    };
}
