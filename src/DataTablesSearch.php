<?php

namespace PatrikTe\DataTablesSearch;
use Symfony\Component\HttpFoundation\Request;


class DataTablesSearch
{

    private $request;

    private $draw = 0;

    private $start = 0;

    private $limit = 10;

    private $filter = [];

    private $columns = [];

    private $sort = [];

    /**
     * CustomSearch constructor.
     * @param $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function getData():array
    {
        $this->findDraw();
        $this->findStart();
        $this->findLength();
        $this->findFilter();
        $this->findStart();
        $this->findColumns();
        $this->findSort();

        return [
            'draw' => $this->draw,
            'start' => $this->start,
            'length' => $this->limit,
            'filter' => $this->filter,
            'sort' => $this->sort
        ];
    }

    private function findDraw():void
    {
        if(null !== $this->request->query->get('draw')){
            $this->draw = (int)$this->request->query->get('draw');
        }
    }

    private function findStart():void
    {
        if(null !== $this->request->query->get('start')){
            $this->start = (int)$this->request->query->get('start');
        }
    }

    private function findLength():void
    {
        if(null !== $this->request->query->get('length')){
            $this->limit = (int)$this->request->query->get('length');
        }
    }

    private function findFilter(): void
    {

        foreach ($this->request->query->all() AS $key => $filter){

            if(0 === strpos($key,'f_') && ('' !== $filter)){

                if(\substr($key, -10, 10) === "_daterange"){

                    $range = \explode('-', $filter);

                    if(\count($range) === 2){
                        $range[0] = \DateTime::createFromFormat('d/m/Y', \trim($range[0]))->setTime(0,0,0);
                        $range[1] = \DateTime::createFromFormat('d/m/Y', \trim($range[1]))->setTime(23,59,59);
                        $this->filter[\substr($key, 2, -10)]['range'] = $range;
                    }
                }else {
                    $this->filter[\substr($key, 2)] = $filter;
                }
            }
        }
    }

    private function findColumns(): void
    {
        if(null === $this->request->query->get('columns') && !\is_array($this->request->query->get('columns'))){
            return;
        }

        foreach($this->request->query->get('columns') AS $filter){
            $this->columns[$filter['data']] = $filter['name'];
        }
    }

    private function findSort():void
    {
        if(null === $this->request->query->get('order') && !\is_array($this->request->query->get('order'))){
            return;
        }

        foreach($this->request->query->get('order') AS $filter){

            if($this->columns[$filter['column']] !== null){
                $this->sort[$this->columns[$filter['column']]] = $filter['dir'];
            }
        }
    }


}