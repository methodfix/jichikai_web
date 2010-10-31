<?php
class HatenaSyntax{
	var $result="";
	var $currentline="";
	var $currentlinetext="";
	var $lastTagFlag="";
	var $lastTagFlagNum="";
	var $currentlistTag="";
	function liSyntax($linenumber){
		$l=$this->$currntline;
		$identifie=substr($l,0,1);
		if($identifie=="-"){
			$tag="ul";
		}else{
			$tag="ol";
		}
		$pattern="/\\".$identifie."*/";
		preg_match($pattern,$l,$matches,PREG_OFFSET_CAPTURE);
		$match=$matches[0][0];
		$n=strlen($match);
		$text=substr($l,$n,-1);
		$this->$currentlinetext=$text;
		if($linenumber==0){
			//最初の行の場合　but ほとんどのケースはtitle記法のはずなのでテスト用
			$this->$currentlistTag=str_repeat("<".$tag.">",$n)."\n"."<li>";
		}elseif($tag===$this->$lastTagFlag){
			//前の列と同じタグの種類の場合
			if($n==$this->$lastTagFlagNum){
				//前の列と同じレベルの場合
				$this->$currentlistTag="<li>";
			}elseif($n>$this->$lastTagFlagNum){
				//前の列より高いレベルの場合
				$diff=$n-$this->$lastTagFlagNum;
				$this->$currentlistTag=str_repeat("<".$tag.">",$diff)."\n"."<li>";
			}elseif($n<$this->$lastTagFlagNum){
				//前の列より低いレベルの場合
				$diff=$this->$lastTagFlagNum-$n;
				$this->$currentlistTag=str_repeat("</".$tag.">",$diff)."\n"."<li>";
			}
		}else{
			//前の列と違うタグの場合
			//まず前のタグを閉じる
			$cn=intval($this->$lastTagFlagNum);
			$closetag=str_repeat("</".$this->$lastTagFlag.">",$cn)."\n";
			//それから新たなタグを付ける
			$this->$currentlistTag=$closetag.str_repeat("<".$tag.">",$n)."\n"."<li>";
		}
		$this->$lastTagFlag=$tag;
		$this->$lastTagFlagNum=$n;
	}
	function ConvertHatenaSyntax($t){
		$lines=preg_split('/\n/',$t);
		$result="";
		for($i=0;$i<count($lines);$i++){
			$l=$lines[$i];
			//$this->$currentline=$l;
			//行頭にキーワードがあるかをチェック
			if(preg_match("/^[\+-]/",$this->$currentline)){
				//list記法
				echo$this->currentline;
				$this->liSyntax($i);
			}elseif(preg_match("/^[\*]/",$this->$currentline)){
				//タイトル記法
				//$this->titleSyntax();
			}
			//block系の記法の目印をチェック
			elseif(preg_match("/^[\*]/",$this->$currentline)){
				//pre記法
				
			}
			//それ以外の記法 ex)http記法 image記法
			
			//行を結合する
			
		}//end for
		//echo $this->$result;
	}//end function ConvertHatenaSyntax
}//end class
?>