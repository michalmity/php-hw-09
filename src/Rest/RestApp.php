<?php declare(strict_types=1);

namespace Books\Rest;

use Books\Middleware\JsonBodyParserMiddleware;
use Books\Rest\BooksRepository;
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

        $this->app->get('/', function (Request $request, Response $response) {
            $response->getBody()->write('API běží!');
            return $response;
        });

        $booksRepository = new BooksRepository();

        $this->app->get('/books', function (Request $request, Response $response) use ($booksRepository) {
            $books = $booksRepository->getAll();
            
            $payload = json_encode($books);
            $response->getBody()->write($payload);
            
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
        });

        $this->app->post('/books', function (Request $request, Response $response) use ($booksRepository) {

            $authHeader = $request->getHeaderLine('Authorization');
            
            if ($authHeader !== 'Basic YWRtaW46cGFzJHdvcmQ=') {
                return $response->withStatus(401); 
            }


            $data = $request->getParsedBody();

            // validace
            if (!isset($data['name'], $data['author'], $data['publisher'], $data['isbn'], $data['pages'])) {
                return $response->withStatus(400); // Bad Request
            }

            $id = $booksRepository->create($data);

            return $response
                ->withHeader('Location', "/books/$id")
                ->withStatus(201);
        });
    }

    public function run(): void {
        $this->app->run();
    }

    public function getApp(): App {
        return $this->app;
    }
}