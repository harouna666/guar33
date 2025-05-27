#!/usr/bin/node
// Пример скрипта на NodeJS для работы на учебном веб-сервере в режиме CGI
// Для запуска поместить в каталог www на учебном сервере и зайти в браузере на http://uXXXXX.kubsu-dev.ru/index.jss
console.log('Status: 404 Not Found');
console.log('Content-Type: text/html');
console.log('');
console.log('<h1>Server Side JavaScript is working!<h1>')
