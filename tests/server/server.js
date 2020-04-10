"use strict";

let app = require('express')();

app.get('/', function (request, response) {
    response.end('<body><p>Home page</p><a href="/link1">Link1</a></body>');
});

app.get('/link1', function (request, response) {
    response.end('<body><p>Page link1</p> <a href="/link3">Link3</a> <a href="/link2.2">Link2</a></body>');
});

app.get('/link2', function (request, response) {
    response.end('<body><p>Page link2</p> <a href="/link4">Link4</a> <a href="https://facebook.com">Facebook</a></body>');
});

app.get('/link3', function (request, response) {
    response.end('<body><p>Page link3</p> <a href="#not-allowed">Self reference</a>  <a href="?question-mark">No allow ?</a> <a href="/link4">Link4</a></body>');
});

app.get('/link4', function (request, response) {
    response.end('<body><p>Page link4</p> <a href="#self-reference">Self reference</a> <a href="/">home page</a> <a href="/link5">Link5</a> </body>');
});

app.get('/link5', function (request, response) {
    response.end('<p>Page link5, link6 should not be extracted since it is not wrapped by hmtl body tag</p> <a href="/link6">Link6</a></body>');
});

let server = app.listen(8080, function () {
    const host = 'localhost';
    const port = server.address().port;

    console.log('Testing server listening at http://%s:%s', host, port);
});
