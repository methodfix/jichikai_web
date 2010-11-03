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
			$this->currentlistTag=str_repeat("<".$tag.">",$n)."\n"."<li>".$this->currentlinetext."</li>\n";
		}elseif($tag===$this->lastTagFlag){
			//前の列と同じタグの種類の場合
			if($n===$this->lastTagFlagNum){
				//前の列と同じレベルの場合
				$this->currentlistTag="<li>".$this->currentlinetext."</li>\n";
			}elseif($n>$this->lastTagFlagNum){
				//前の列より高いレベルの場合
				$diff=$n-$this->lastTagFlagNum;
				$this->currentlistTag=str_repeat("<".$tag.">",$diff)."\n"."<li>".$this->currentlinetext."</li>\n";
			}elseif($n<$this->lastTagFlagNum){
				//前の列より低いレベルの場合
				$diff=$this->lastTagFlagNum-$n;
				$this->currentlistTag=str_repeat("</".$tag.">",$diff)."\n"."<li>".$this->currentlinetext."</li>\n";
			}
		}else{
			//前の列と違うタグの場合
			//まず前のタグを閉じる
			$closetag=$this->listTagClosing();
			//それから新たなタグを付ける
			$this->currentlistTag=$closetag.str_repeat("<".$tag.">",$n)."\n"."<li>".$this->currentlinetext."</li>\n";
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
			$this->lastTagFlag="done";
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
	function inlineSyntaxes(){
		$l=$this->currentline;
		preg_match_all("/\[.*?:.*?\]/",$l,$matches,PREG_OFFSET_CAPTURE);
		foreach($matches[0] as $match){
			$wasundefined=false;
			$m=$match[0];
			$keys=preg_split("/:/",substr($m,1,-1));
			switch($keys[0]){
				case"http":
					$url=$keys[0].":".$keys[1];
					if(preg_match("/title=.*/",$keys[2])){
						$title=preg_replace("/title=(.*)/","$1",$keys[2]);
					}
					$inlinehtml=$title?"<a href='$url' target='_blank'>$title</a>":"<a href='$url' target='_blank'>$url</a>";
					break;
				case"image":
					$inlinehtml="<img src='/img/".$keys[1]."'>";
					break;
				default:
					$wasundefined=true;
					break;
			}
			if(!$wasundefined){
				$this->currentlinehtml=preg_replace('/'.preg_quote($m,"/").'/',$inlinehtml,$this->currentline);
			}
		}
	}
	function ConvertHatenaSyntax($t){
		$lines=preg_split('/\n/',$t);
		$result="";
		for($i=0;$i<count($lines);$i++){
			$this->currentlinehtml="";
			$l=$lines[$i];
			$this->currentline=$l;
			$pre=false;
			//行頭にキーワードがあるかをチェック
			if(preg_match("/^[\+-]/",$this->currentline)){
				//list記法
				$this->liSyntax($i);
				
			}elseif(preg_match("/^\*/",$this->currentline)){
				$this->currentlinehtml.=$this->listTagClosing();
				//タイトル記法
				$this->titleSyntax();
			}
			elseif(preg_match("/^\|/",$this->currentline)){
				//table記法
				$this->currentlinehtml.=$this->listTagClosing();
				while(preg_match("/^\|/",$lines[$i])){
					$cells=preg_split("/\|/",$lines[$i]);
					$innerTable.="<tr>";
					foreach($cells as $cell){
						if(!$cell){continue;}
						$innerTable.="<td>".$cell."</td>\n";
					}
					$innerTable.="</tr>\n";
					$i++;
				}
				$this->currentline="<table>\n".$innerTable."</table>\n";
			}
			elseif(preg_match("/^>>$/",$this->currentline)){
				//pre記法
				$this->currentlinehtml.=$this->listTagClosing();
				$this->currentlinehtml.="<pre>\n";
				while(true){
					if(!preg_match("/^<<$/",$lines[++$i])){
						$this->currentlinehtml.=$lines[$i]."\n";
					}else{
						break;
					}
				}
				$this->currentlinehtml.="</pre>\n";
				//$i++;
				$pre=true;
			}
			
			if($this->lastTagFlag==="ol"||$this->lastTagFlag==="ul"){
				$this->currentlinehtml.=$this->listTagClosing();
			}
			//それ以外の記法 ex)http記法 image記法
			if(!$pre&&preg_match("/\[.*:.*\]/",$this->currentline)){
				$this->inlineSyntaxes();
			}
			//行を結合する
			$this->result.=$this->currentlinehtml?$this->currentlinehtml:$this->currentline."<br />";
			
			
		}//end for
		return $this->result;
	}//end function ConvertHatenaSyntax
}//end class
?>