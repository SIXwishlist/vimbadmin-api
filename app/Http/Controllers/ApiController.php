<?php 

namespace App\Http\Controllers;

use App\VbaModels\Domain;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\Collection;
use League\Fractal\TransformerAbstract;
use League\Fractal\Serializer\JsonApiSerializer;
use Illuminate\Http\Response as IlluminateResponse;

class ApiController extends Controller 
{
    /**
     * @var Domain
     */
    protected $domain;

    /**
     * @var TransformerAbstract
     */
    protected $transformer;

    /**
     * @var Manager
     */
    protected $fractal;

    /**
     * @param Domain              $domain
     * @param TransformerAbstract $transformer
     * @param Manager             $fractal
     */
    public function __construct(Domain $domain, TransformerAbstract $transformer, Manager $fractal)
    {
        $this->domain = $domain;
        $this->transformer = $transformer;
        $this->fractal = $fractal;
        $domainName = $this->routeParameter('domainName');
        $baseUrl = url(is_null($domainName)?'':'/'.$domainName);
        $fractal->setSerializer(new JsonApiSerializer($baseUrl));
    }

    /**
     * Helper to grab the domain object.
     * 
     * @param  string $domainName
     * @return App\VbaModels\Domain
     */
    protected function getDomain($domainName)
    {
        return $this->domain->where('domain', $domainName)->firstOrFail();
    }

    /**
     * Transform a colletion of objects.
     * 
     * @param  mixed $items
     * @return array
     */
    protected function transformCollection($items)
    {
        $collection = new Collection($items, $this->transformer, $this->type);

        return $this->fractal->createData($collection)->toArray();
    }
    
    /**
     * Transform a single Item.
     * 
     * @param  mixed $item
     * @return array
     */
    protected function transformItem($item)
    {
        $item = new Item($item, $this->transformer, $this->type);

        return $this->fractal->createData($item)->toArray();
    }

    protected $statusCode = IlluminateResponse::HTTP_OK;

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    public function respond($data, $headers = [])
    {
        return response()->json($data, $this->getStatusCode(), $headers);
    }

    /**
     * Get a given parameter from the route.
     * https://gist.github.com/irazasyed/8ce3b004177ce23af5ef
     * @param $name
     * @param null $default
     * @return mixed
     */
    protected function routeParameter($name, $default = null)
    {
        $routeInfo = app('request')->route();
        return array_get($routeInfo[2], $name, $default);
    }
}