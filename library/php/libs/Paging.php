<?php

namespace libs;

class Paging {

    const PAGE = 'page';
    const Head = 'head';
    const Limit = 'limit';
    const AllLength = 'allLength';
    const PageLength = 'pageLength';

    private $head;
    private $limit;
    private $allLength = 0;
    private $pageLength;


    public function __construct($page,$pageLength = 20){
        $this->head = $pageLength *( $page - 1 );
        $this->limit = "LIMIT $this->head,$pageLength";
        $this->pageLength = $pageLength;
    }

    public function getPaging(){
        return array(self::Head=>$this->head,self::Limit=>$this->limit,self::AllLength =>$this->allLength,self::PageLength =>$this->pageLength);
    }

    public function setAllLength($allLength){
        $this->allLength = $allLength;
    }

}
