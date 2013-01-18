#!/usr/bin/php
<?php

$seenpages = array();


function parsehtml($page)
{
	global $xml;
	global $sections;
	global $seenpages;
	$pagedata = file_get_contents($page);
	preg_match_all('/<a href=\"(.+)\".*>(.+)<\/a>/U',$pagedata,$matches);

	foreach($matches[1] as $key => $value)
	{
		if(!preg_match('/http/i',$matches[1][$key]) AND $matches[2][$key] != "Home" )
		{
			$matches[1][$key] = preg_replace("/\.\.\//",'',$matches[1][$key]);
			if(!in_array($matches[1][$key],$seenpages))
			{
				$seenpages[] = $matches[1][$key];
				$targetpage = file_get_contents('PrototypeJS.docset/Contents/Resources/Documents/prototypejs/'.$matches[1][$key].'index.html');
				preg_match('/<h2 class="page-title">\s+<span class="type">(.+)<\/span>\s+(.+)\s+<\/h2>/U',$targetpage,$targetmatches);
				$title = $targetmatches[2];
				if(in_array($targetmatches[1],array('instance method','class method')))
				{
					$type = "Method";
				}
				elseif(in_array($targetmatches[1],array('instance property','class property')))
				{
					$type = "Property";
				}
				elseif(in_array($targetmatches[1],array('utility')))
				{
					$type = "Function";
				}
				elseif(in_array($targetmatches[1],array('mixin')))
				{
					$type = "Class";
				}
				else
				{
					$type = ucfirst($targetmatches[1]);
				}
				$xml->startElement('Token');
					$xml->startElement('TokenIdentifier');
						$xml->writeElement('Name',$title);
						$xml->writeElement('Type',$type);
						$xml->writeElement('APILanguage','cpp');
					$xml->endElement();
					$xml->writeElement('Path','prototypejs/'.$matches[1][$key].'index.html');
					$xml->writeElement('Abstract','');
				$xml->endElement();
			}
/*
<Token>
    <TokenIdentifier>
      <Name>:active</Name>
      <Type>cl</Type>
      <APILanguage>cpp</APILanguage>
    </TokenIdentifier>
    <Path>developer.mozilla.org/en-US/docs/CSS/_active.html</Path>
    <Abstract></Abstract>
  </Token>

*/
		}
	}
}

function parsedir($dir)
{
	$files = scandir($dir);
	foreach($files as $key => $f)
	{
		if(!in_array($f,array('.','..','.DS_Store')) AND !preg_match('/\.tar\.bz2/',$f) AND !preg_match('/\.png/',$f) AND !preg_match('/\.js/',$f) AND !preg_match('/\.css/',$f))
		{
			if($f == 'index.html')
			{
				print '+';
				parsehtml($dir.'/'.$f);
			}
			else
			{
				print ".";
				parsedir($dir.'/'.$f);
			}
		}
	}
}

$xml = new XmlWriter();
$xml->openMemory();
$xml->startDocument('1.0','UTF-8');
$xml->startElement('Tokens');
$xml->writeAttribute('version','1.0');


$token_file = 'PrototypeJS.docset/Contents/Resources/Tokens.xml';

parsedir('PrototypeJS.docset/Contents/Resources/Documents/prototypejs');

$xml->endDocument();
file_put_contents($token_file,$xml->outputMemory());

print_r($seenpages);


/*

sample
<Token>
    <TokenIdentifier>
      <Name>:active</Name>
      <Type>cl</Type>
      <APILanguage>cpp</APILanguage>
    </TokenIdentifier>
    <Path>developer.mozilla.org/en-US/docs/CSS/_active.html</Path>
    <Abstract></Abstract>
  </Token>


*/



?>


