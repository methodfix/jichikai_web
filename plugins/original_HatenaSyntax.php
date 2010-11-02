<?php
class HatenaSyntax{
	var $result="";
	var $currentline="";
	var $currentlinetext="";
	var $currentlinehtml="";
	var $lastTagFlag="";
	var $lastTagFlagNum="";
	var $currentlistTag="";
	function liSyntax($linenumber){
		$l=$this->currentline;
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
		$text=substr($l,$n,strlen($l)-1);
		$this->currentlinetext=$text;
		if($linenumber===0){
			//最初の行の場合　but ほとんどのケースはtitle記法のはずなのでテスト用
			$this->currentlistTag=str_repeat("<".$tag.">",$n)."\n"."<li>".$this->currentlinetext."</li>";
		}elseif($tag===$this->lastTagFlag){
			//前の列と同じタグの種類の場合
			if($n===$this->lastTagFlagNum){
				//前の列と同じレベルの場合
				$this->currentlistTag="<li>".$this->currentlinetext."</li>";
			}elseif($n>$this->lastTagFlagNum){
				//前の列より高いレベルの場合
				$diff=$n-$this->lastTagFlagNum;
				$this->currentlistTag=str_repeat("<".$tag.">",$diff)."\n"."<li>".$this->currentlinetext."</li>";
			}elseif($n<$this->lastTagFlagNum){
				//前の列より低いレベルの場合
				$diff=$this->lastTagFlagNum-$n;
				$this->currentlistTag=str_repeat("</".$tag.">",$diff)."\n"."<li>".$this->currentlinetext."</li>";
			}
		}else{
			//前の列と違うタグの場合
			//まず前のタグを閉じる
			$closetag=$this->listTagClosing();
			//それから新たなタグを付ける
			$this->currentlistTag=$closetag.str_repeat("<".$tag.">",$n)."\n"."<li>".$this->currentlinetext."</li>";
		}
		$this->lastTagFlagNum=$n;
		$this->lastTagFlag=$tag;
		$this->currentlinehtml=$this->currentlistTag;
	}
	function listTagClosing(){
		if($this->lastTagFlag==="ol"||$this->lastTagFlag==="ul"){
			$cn=intval($this->lastTagFlagNum);
			$ct="</".$this->lastTagFlag.">";
			$closetag=str_repeat($ct,$cn)."\n";
			return $closetag;
		}else{
			return"";
		}
	}
	function titleSyntax(){
		$l=$this->currentline;
		$pattern="/\\**/";
		preg_match($pattern,$l,$matches,PREG_OFFSET_CAPTURE);
		$match=$matches[0][0];
		$n=strlen($match)+1;
		$text=substr($l,$n-1,strlen($l)-1);
		$this->currentlinehtml.="<h".$n.">".$text."</h".$n.">\n";
	}
	function ConvertHatenaSyntax($t){
		$lines=preg_split('/\n/',$t);
		$result="";
		for($i=0;$i<count($lines);$i++){
			$this->currentlinehtml="";
			$l=$lines[$i];
			$this->currentline=$l;
			//行頭にキーワードがあるかをチェック
			if(preg_match("/^[\+-]/",$this->currentline)){
				//list記法
				$this->liSyntax($i);
				
			}elseif(preg_match("/^\*/",$this->currentline)){
				$this->currentlinehtml.=$this->listTagClosing();
				//タイトル記法
				$this->titleSyntax();
			}
			//block系の記法の目印をチェック
			elseif(preg_match("/^[\*]/",$this->currentline)){
				$this->currentlinehtml.=$this->listTagClosing();
				//pre記法
				
			}
			//それ以外の記法 ex)http記法 image記法
			if(preg_match("/\[http:.*\]/",$this->currentline)){
				
			}elseif(preg_match("/\[image:.*\]/",$this->currentline)){
				
			}
			//行を結合する
			if($this->lastTagFlag==="ol"||$this->lastTagFlag==="ul"){
				$this->currentlinehtml.=$this->listTagClosing();
			}
			$this->result.=$this->currentlinehtml?$this->currentlinehtml:$this->currentline;
			
			
		}//end for
		return $this->result;
	}//end function ConvertHatenaSyntax
}//end class
?>