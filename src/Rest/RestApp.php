<?php declare(strict_types=1);

namespace Books\Rest;

use Books\Middleware\JsonBodyParserMiddleware;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class RestApp
{
    private ?App $app = null;

    public function configure(): void
    {
        $this->app = AppFactory::create();

        $this->app->addRoutingMiddleware();
        $this->app->addErrorMiddleware(true, true, true);
        $this->app->add(new JsonBodyParserMiddleware());

        $this->app->get('/', function (Request $request, Response $response) 
        {
            $response->getBody()->write('Funguje to! Ale nic tady není.');
            return $response;
        });

        // instance repozitare
        $booksRepository = new BooksRepository();

        $this->app->get('/books', function (Request $request, Response $response) use ($booksRepository)
        {
            $books = $booksRepository->getAll();

            $payload = json_encode($books);

            $response->getBody()->write($payload);

            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
        });

    }

    public function run(): void {
        $this->app->run();
    }

    public function getApp(): App {
        return $this->app;
    }
}
