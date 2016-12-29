<?php

class JSONParse
{
	static $version='1.0';
	static $host='http://127.0.0.1:9501';
	static $labels=[
		'href',
		'links',
		'items',
		'queries',
		'template',
		'error',
		];

	public function setHref($suf){
		$this->coll['href']=static::$host.$suf;
		return $this;
	}

	public function setError($title,$code,$msg){
		$this->coll['error']=[
			'title'=>$title,
			'code'=>$code,
			'message'=>$msg,
			];
		return $this;
	}

	// accepted data: name:prompt
	public function setTemplate($data){
			foreach($data as $name=>$prompt){
				$data_arr[]=[
					'name'=>$name,
					'value'=>'',
					'prompt'=>$prompt,
					];
		 }

		if(isset($data_arr)){
			$this->coll['template']=['data'=>$data_arr];
		}
		return $this;
	}

	// accepted data: name:value
	public function setQueries($suf,$prompt,$data)
	{
		$qu_arr['rel']='search';
		$qu_arr['href']= static::$host.$suf.'/search';
		$qu_arr['prompt']=$prompt;

		foreach($data as $name=>$value)
		{
			$data_arr[]=[
				'name'=>$name,
				'value'=>$value,
					];
		}

		if(isset($data_arr))
		{
			$qu_arr['data']=$data_arr;
		}

		$this->coll['queries']=$qu_arr;
		return $this;
	}

	// accepted data: rel:href
	public function setLinks($data)
	{
		foreach($data as $rel=>$href)
		{
			$data_arr[]=[
				'rel'=>$rel,
				'href'=>$href
					];
		}

		if(isset($data_arr))
		{
			$this->coll['links']=$data_arr;
		}
		return $this;
	}

	// accepted rec_arr: pk:data & link
	// data: name:value link: rel:href
	public function setItems($rec_arr)
	{
		foreach($rec_arr as $pk=>$dl)
		{
			$item_arr['href'] = static::$host.'/'.$pk;
			// data and link
			if(array_key_exists('data',$dl))
			{
				foreach($dl['data'] as $name=>$value){
					$data_arr[]=[
						'name'=>$name,
						'value'=>$value,
						];
				}
				if(isset($data_arr))
				{
					$item_arr['data']=$data_arr;
					unset($data_arr);
				}
			}

			// may have links
			if(array_key_exists('links',$dl))
			{
				foreach($dl['links'] as $rel=>$href)
				{
					$links_arr[]=[
						'rel'=>$rel,
						'href'=>$href
							];
				}
				if(isset($links_arr))
				{
					$item_arr['links']=$links_arr;
					unset($links_arr);
				}
			}
			$items_arr[]=$item_arr;
			unset($item_arr);
		}

		if(isset($items_arr))
		{
			$this->coll['items']=$items_arr;
		}
		return $this;
	}

	public function parseRawData($raw_data)
	{
		$data = json_decode($raw_data,true);
		if( is_array($data) &&array_key_exists('template',$data)&&array_key_exists('data',$data['template']) ){
			$ret_arr=array_column($data['template']['data'],'value','name');
		}
		return !empty($ret_arr)?$ret_arr:false;
	}

	// accept an array to fill this object
	public function fillWithArr($arr){
		if(!is_array($arr)){
			return;
		}
		if(array_key_exists('error',$arr)){
			$e=$arr['error'];
			$this->setError(
					$e['title'],
					$e['code'],
					$e['msg']
					);
			// error happens no more scan is needed
			return;
		}

		if(array_key_exists('href',$arr))
		{
			$this->setHref($arr['href']);

			if(array_key_exists('links',$arr))
				$this->setLinks($arr['links']);

			if(array_key_exists('items',$arr))
				$this->setItems($arr['items']);

			if(array_key_exists('queries',$arr)){
				$q=$arr['queries'];
				$this->setQueries(
						$arr['href'],
						$q['prompt'],
						$q['data']);
			}

			if(array_key_exists('template',$arr)){
					$this->setTemplate($arr['template']);
			}
		}
	}

	public function getEncodedStr(){
		$col_arr['version']=static::$version;

		if(isset($this->coll)){
			foreach(static::$labels as $l){
				if(array_key_exists($l,$this->coll)){
					$col_arr[$l]=$this->coll[$l];
				}
			}
		}

		return json_encode(['collection'=>$col_arr]);
	}

	public function cleanUp(){
		unset($this->coll);
	}


}
?>
