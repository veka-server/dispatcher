# Dispatcher
Un dispatcher PSR-7 et PSR-15 ultra minimaliste.
Il est basé sur tuto de grafikart.fr sur les middleware PSR.
Il sera ammené a evolué pour maintenir les normes PSR.

# Utilisation
Création de l'instance du dispatcher
```php
// creation du dispatcher
$Dispatcher = new VekaServer\Dispatcher\Dispatcher();
```

Ajout des middlewares
```php
// ajout des middlewares
$Dispatcher
    ->pipe(new \Middlewares\Whoops())
    ->pipe(new VK\Framework\MyMiddleware())
    ->pipe(new VK\Framework\MyMiddlewareA());
```

Création de la requete PSR-7 a traiter via GuzzleHttp
```php
// recuperation de la requete recue
$request = \GuzzleHttp\Psr7\ServerRequest::fromGlobals();
```

Execute les middlewares sur la requete créer precedement et recupere la reponse
```php
// lance l'execution des middlewares et recupere la reponse
$response = $Dispatcher->process($request);
```

Affiche la reponse a l'ecran
```php
// si la reponse est presente ont l'affiche
if($response instanceof \Psr\Http\Message\ResponseInterface)
    send($response);
```

