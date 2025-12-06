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

        // GET /books
        $this->app->get('/books', function (Request $request, Response $response) use ($booksRepository) 
        {
            $books = $booksRepository->getAll();
            
            $payload = json_encode($books);
            $response->getBody()->write($payload);
            
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
        });

        // POST /books
        $this->app->post('/books', function (Request $request, Response $response) use ($booksRepository) 
        {

            $auth = $request->getHeaderLine('Authorization');
            if ($auth !== 'Basic ' . base64_encode('admin:pas$word')) 
            {
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


        // GET - /books/id
        $this->app->get('/books/{id}', function (Request $request, Response $response, array $args) use ($booksRepository) 
        {
            $id = $args['id'];

            if (!is_numeric($id)) {
                return $response->withStatus(400);
            }

            $book = $booksRepository->getById((int)$id);

            if ($book === null) {
                return $response->withStatus(404);
            }

            $response->getBody()->write(json_encode($book));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
        });

        // PUT - /books/id (upraveni knihy)
        $this->app->put('/books/{id}', function (Request $request, Response $response, array $args) use ($booksRepository) 
        {
            $auth = $request->getHeaderLine('Authorization');
            if ($auth !== 'Basic ' . base64_encode('admin:pas$word')) 
            {
                return $response->withStatus(401);
            }

            $id = (int)$args['id'];

            $existingBook = $booksRepository->getById($id);
            if ($existingBook === null)
            {
                return $response->withStatus(404);
            }

            $data = $request->getParsedBody();
            if (!isset($data['name'], $data['author'], $data['publisher'], $data['isbn'], $data['pages']))
            {
                return $response->withStatus(400);
            }

            $booksRepository->update($id, $data);

            return $response->withStatus(204);
        });

        // DELETE /books/id
        $this->app->delete('/books/{id}', function (Request $request, Response $response, array $args) use ($booksRepository) {
           
            $auth = $request->getHeaderLine('Authorization');
            if ($auth !== 'Basic ' . base64_encode('admin:pas$word')) 
            {
                return $response->withStatus(401);
            }

            $id = (int)$args['id'];

            $existingBook = $booksRepository->getById($id);
            if ($existingBook === null) 
            {
                return $response->withStatus(404);
            }

            $booksRepository->delete($id);

            return $response->withStatus(204);
        });

    }

    public function run(): void {
        $this->app->run();
    }

    public function getApp(): App {
        return $this->app;
    }
}