# Alkane PHP
    The PHP Framework for easy and flexible Development

# Basic Usages

    # HTML Page render
    $layout = new Alkane\HtmlPageLayout\HtmlPageLayout();
    $layout->setTitle('Alkane PHP');
    $layout->setDescription('App Description');
    $layout->setKeywords('App, Description');
    $layout->setFavicon('resource/images/favicon.ico');
    $layout->setAuthor('Sadiq');
    $layout->setRobots('index, follow');
    $layout->setCss([
        'https://fonts.googleapis.com/css?family=Lato:300,400,700&display=swap'
    ]);
    $layout->setJs([
        _BASE_URL_ . 'resource/scripts/functions.js'
    ]);
    $layout->render();

    # Router
    Alkane\Router\Router::get('/', 'app/index.php');
    Alkane\Router\Router::get('/user/{id}', 'app/index.php');

    ------------------- or -------------------------
    "route.json"
    {
        "file" : {
            "#_route_name" : "#_route_file_location",
            "/"            : "app/index.php",
            "/user/{id}"   : "app/index.php"
        }
    }


    /* ^^^^^^^^^^^^ GET user ID from "app/index.php" ^^^^^^^^
        // if client send a request > "HTTP/1.1 GET /user/7896"
        print_r(Alkane\Router\Router::get_request_params());
        /* Result ^^^^^^^^^^^^^^^^^
            Array (
                [id] => 7896
            )
        **************************/
    **********************************************************/

    # Database
    $dbInstance = Database::getInstance(); // or > new Database($custom_connection_name);
    $sql = new SqlQuery($dbInstance);
    $sql->select([      // or > $sql->select() // for * all
            'ID',
            'name',
            'email'
        ])
        ->from('table')
        ->where('ID = :id', [
            'id' => 20
        ]);
    $result = $sql->exec();
    print_r($result->fetch(SqlQuery::FETCH_ASSOC));

    ----------------------- or ----------------------------
    --------------- Using Crud Controler ------------------

    $crud = new Alkane\CrudControler\CrudControler;
    $crud->setTableName('table');
    $crud->setTablePrimaryKey('ID');
    $crud->setColumnNames([
        'name',
        'email'
    ]);
    print_r($crud->read($id));


    # Session
    Alkane\SessionControler\SessionControler::set('mail/smtp/host', 'smtp.gmail.com');
    Alkane\SessionControler\SessionControler::set('mail/smtp/user', 'user@gmail.com');
    Alkane\SessionControler\SessionControler::set('mail/smtp/password', '6456g654d26gv624');

    // get data back
    print_r(Alkane\SessionControler\SessionControler::get('mail/smtp'));
    /* ^^^^^^^^^^^ result ^^^^^^^^^^^^
        Array (
            [host] => smtp.gmail.com
            [user] => user@gmail.com
            [password] => 6456g654d26gv624
        )
    *********************************/


# Apache rewrite rule for Router
    RewriteEngine On
    RewriteRule ^(.*)$ index.php [L,QSA]
    ErrorDocument 400 /index.php
    ErrorDocument 401 /index.php
    ErrorDocument 403 /index.php
    ErrorDocument 404 /index.php
    ErrorDocument 500 /index.php
    ErrorDocument 502 /index.php
    ErrorDocument 503 /index.php


# Nginx rewrite rule for Router
    location / {
        rewrite ^(.*)$ /index.php?$1 last;   
    }

