<?php
// Suppression des fichiers temporaires vieux de plus de trois minutes.
// On laisse les plus récents, pour faciliter les débogages.
$folder = new DirectoryIterator('tmp/');
foreach($folder as $file)
	if($file->isFile() && !$file->isDot() && (time() - $file->getMTime() > 180))
		unlink($file->getPathname());
$font=$_REQUEST['font'];
$size=$_REQUEST['fontsize'];
$initialfont=$_REQUEST['initial'];
$initialsize=$_REQUEST['initialsize'];
$initialraise=$_REQUEST['initialraise'];
$annotraise=$_REQUEST['annotraise'];
$spacelinestext=$_REQUEST['spacelinestext'];
$spacebeneathtext=$_REQUEST['spacebeneathtext'];
$factor=$_REQUEST['factor'];
$gabc=$_REQUEST['gabc'];
$guid=$_REQUEST['guid'];
$filename=$_REQUEST['filename'];
$format=$_REQUEST['fmt'];
$width=$_REQUEST['width'];
$height=$_REQUEST['height'];
$margin=$_REQUEST['margin'];
$spacing=$_REQUEST['spacing'];
$red=$_REQUEST['red'];
$green=$_REQUEST['green'];
$blue=$_REQUEST['blue'];
$save=$_REQUEST['save'];
$croppdf=true;
$colorlines=true;
$colorsym=true;
$colortit=true;
$colorann=true;
$colorcom=true;
$colorlet=true;
if($size) {
  $sizeCmd = "\\fontsize{{$size}}{{$size}}\\selectfont";
} else {
  $sizeCmd = "\\fontsize{12}{12}\\selectfont";
}
if($factor) {
  $grefactorCmd = "\\setgrefactor{{$factor}}";
} else {
  $grefactorCmd = "\\setgrefactor{17}";
}
if($font == 'palatino') {
  $sizeCmd = '';
}// else if($font=='GaramondPremierPro'){
 // $initialFormat = '{\\garamondInitial #1}}';
//}
if($_REQUEST['croppdf']=='false'){
  $croppdf=false;
}
if($_REQUEST['colorlines']=='false'){
  $colorlines=false;
}
if($_REQUEST['colorsym']=='false'){
  $colorsym=false;
}
if($_REQUEST['colortit']=='false'){
  $colortit=false;
}
if($_REQUEST['colorann']=='false'){
  $colorann=false;
}
if($_REQUEST['colorcom']=='false'){
  $colorcom=false;
}
if($_REQUEST['colorlet']=='false'){
  $colorlet=false;
}
if($width==''){
  $width='148';
}
if($height==''){
  $height='210';
}
if($margin==''){
  $margin='12';
}
if($red==''){
  $red='152';
}
if($green==''){
  $green='0';
}
if($blue==''){
  $blue='0';
}
if($factor==''){
  $factor='17';
}
if($factor<17){
  $interligne = 17/$factor;
} else {
  $interligne = 1;
}
$initialFontSize = 2 * $factor;
$initialFormat = "{\\fontsize{{$initialFontSize}}{{$initialFontSize}}\\selectfont #1}}";
ini_set('magic_quotes_runtime', 0);
if($format=='eps') {
  $ftmmime='application/eps';
} else if($format=='json') {
  $ftmmime='application/json';
} else if($format=='zip') {
  $ftmmime='application/json';
} else {
  $format = 'pdf';
  $fmtmime='application/pdf';
}
if($gabc=='') {
  header("Content-type: application/json");
  if($guid) {
    $dir = "scores/square/sandbox/$guid";
    if(is_dir($dir)) {
      exec("zip -1j $dir $dir/*");
      $result = array("href" => "$dir.zip");
    } else {
      $result = array("error" => "Guid not found: $guid");
    }
  } else {
    $result = array("error" => "No directory given to ZIP");
  }
  echo json_encode($result);
} else {
  if(is_array($gabc)) {
    $gabcs = $gabc;
  } else {
    $gabcs = Array($gabc);
  }

  $dir = 'tmp';
  $ofilename = uniqid('gregorio',true);
  $tmpfname = "tmp/$ofilename";
  if($guid) {
    $dir .= "/$guid";
    if(!is_dir($dir)) {
      mkdir($dir);
    }
    $ofilename = $filename;
  }
  $nametex = "$tmpfname.tex";
  $namedvi = "$tmpfname.dvi";
  $namepdf = str_replace('\'','',"$tmpfname.pdf");
  $namelog="$tmpfname.log";
  $nameaux="$tmpfname.aux";
  $tmpfnameS = str_replace('\'','\\\'',$tmpfname);
  $nametexS = str_replace('\'','\\\'',$nametex);
  $namedviS = str_replace('\'','\\\'',$namedvi);
  $namepdfS = str_replace('\'','\\\'',$namepdf);
  $namelogS = str_replace('\'','\\\'',$namelog);
  $nameauxS = str_replace('\'','\\\'',$nameaux);

  if($format=='eps'){
    $finalpdf = "$dir/$ofilename.eps";
  } else {
    $finalpdf = "$dir/$ofilename.pdf";
  }
  $finalpdfS = str_replace('\'','\\\'',$finalpdf);

  $pwidth=$width;
  $pheight=$height;
  $papercmd="%\\usepackage{vmargin}
%\\setpapersize{custom}{{$pwidth}mm}{{$pheight}mm}
%\\setmargnohfrb{12mm}{12mm}{12mm}{12mm}
\\usepackage[papersize={{$pwidth}mm,{$pheight}mm},margin={$margin}mm]{geometry}
\\special{ pdf: pagesize width {$pwidth}truemm height {$pheight}truemm}";
  $includeScores = '';
  foreach($gabcs as $i => $gabc) {
    $theader = substr($gabc,0,strpos($gabc,'%%'));
    if(preg_match('/%%(?:\s*(?:\([^)]*\))*)+(\S)/u',$gabc, $match)){
      $initial=$match[1];
    }
    $header = array();
    $pattern = '/(?:^|\\n)([\w-_]+):\s*([^;\\r\\n]+)(?:;|$)/i';
    $offset = 0;
    if(preg_match_all($pattern, $theader, $matches)>0){
      foreach($matches[1] as $key => $value){
        if(!$header[$value]) {
          $header[$value] = $matches[2][$key];
        } else {
          if(!$header[$value . 'Array']) {
            $header[$value . 'Array'] = array();
            $header[$value . 'Array'][] = $header[$value];
          }
          $header[$value . 'Array'][] = $matches[2][$key];
        }
      }
    }

    $namegabc = "$tmpfname.$i.gabc";
    $namegtex = "$tmpfname.$i.tex";
    $namegabcS = str_replace('\'','\\\'',$namegabc);
    $namegtexS = str_replace('\'','\\\'',$namegtex);

    $spacingcmd = '';
    if($spacing!=''){
      //$spacingcmd = "\GreLoadSpaceConf{{$spacing}}";
      $spacingcmd = "\\greremovetranslationspace\\greremovespaceabove\\input gsp-$spacing.tex\\greadaptconfvalues\\gresetverticalspaces\\global\\divide\\greadditionallineswidth by \\grefactor%";
    }
    $italicline = $header['commentary'];
    $commentcmd = '';
    $usernotesline = $header['user-notes'];
    if($usernotesline != '' or $italicline != ''){
      $commentcmd = "\\dualcomment{{$usernotesline}}{{$italicline}}";
    }
    $annotation = $header['annotation'];
    $annotationTwo = $header['annotationArray'][1];
    if($annotationTwo == $annotation) {
      $annotationTwo = '';
    }
    $titlecmd = $header['name'] == ''? '' : "\\begin{center}\\begin{huge}\\rubrumtit\\textsc{{$header['name']}}\\end{huge}\\end{center}\\bigskip";
    $annotcmd = '';
    if($annotation) {
      $annotsuffix='';
      if(preg_match('/[^a-z]+([a-g]\d?\*?\s*)$/',$annotation, $match)){
        $annotsuffix=$match[1];
        $annotation = substr($annotation,0,strlen($annotation) - strlen($annotsuffix));
      }
      $annotation = preg_replace_callback(
        '/\b[A-Z\d]+\b/',
        create_function(
          '$matches',
          'return strtolower($matches[0]);'
        ),
        $annotation
      );
      $annotsize = 4*$size/5;
      $annotation = "{\\rubrumann $annotation}";
      if($font == 'Georgia') {
        $upperAnnot = strtoupper($annotation);
        $annothelper = "{\\fontsize{{$annotsize}}{{$annotsize}}\\selectfont {$upperAnnot}$annotsuffix}";
      } else {
        $annothelper = "{\\fontsize{{$annotsize}}{{$annotsize}}\\selectfont\\textsc{{$annotation}}$annotsuffix}";
      }
      $annotcmd = "\\def\\annot{\\raisebox{{$annotraise}pt}[1.2\\height][1ex]{{$annothelper}}}";
    } else {
      $annotcmd = "\\def\\annot{}";
    }
    if($annotationTwo) {
      $annotsuffix='';
      if(preg_match('/[^a-z]+([a-g]\d?\*?\s*)$/',$annotation, $match)){
        $annotsuffix=$match[1];
        $annotationTwo = substr($annotationTwo,0,strlen($annotationTwo) - strlen($annotsuffix));
      }
      $annotationTwo = preg_replace_callback(
        '/\b[A-Z\d]+\b/',
        create_function(
          '$matches',
          'return strtolower($matches[0]);'
        ),
        $annotationTwo
      );
      $annotationTwo = "{\\rubrumann $annotationTwo}";
      if($font == 'Georgia') {
        $upperAnnot = strtoupper($annotationTwo);
        $annothelperTwo = "{\\fontsize{{$annotsize}}{{$annotsize}}\\selectfont {$upperAnnot}$annotsuffix}";
      } else {
        $annothelperTwo = "{\\fontsize{{$annotsize}}{{$annotsize}}\\selectfont\\textsc{{$annotationTwo}}$annotsuffix}";
      }
      //$annotcmd .= "\\gresetsecondlineaboveinitial{{$annothelperTwo}}{{$annothelperTwo}}";
      $annotcmd .= "\\def\\annottwo{\\raisebox{{$annotraise}pt}[1.2\\height][1ex]{{$annothelperTwo}}}";
    } else {
      $annotcmd .= "\\def\\annottwo{}";
    }
    if($annotcmd != ''){
      if($initial) {
        $annotcmd .= "\\setinitialspacing{{$initial}}";
      } else {
        $annotcmd .= "\\setinitialspacing{?}";
      }
    }

    // write out gabc
    $handle = fopen($namegabc, 'w');
    if(!$handle){
      $result = array("error" => "Unable to create file $namegabc");
      header("Content-type: application/json");
      echo json_encode($result);
      return;
    }
    fwrite($handle, "\xEF\xBB\xBF$gabc");
    fclose($handle);


    $includeScores .= "$titlecmd
$commentcmd
$grefactorCmd

%\\setgrefactor{17}
$spacingcmd
$annotcmd
\\gretranslationheight = 0.1904in
\\grespaceabovelines=0.1044in
$sizeCmd
\\UseAlternatePunctumCavum{\\includescore{{$namegtex}}}

";
    // run gregorio
    exec("gregorio $namegabcS 2>&1", $gregOutput, $gregRetVal);

    if($gregRetVal){
      $result = array("error" => implode("\n",$gregOutput));
      header("Content-type: application/json");
      echo json_encode($result);
      return;
    }
  }
if($initialfont==''){
    $initialfont='l800';
}
if($initialsize==''){
    $initialsize=36;
}
if($initialraise==''){
    $initialraise=0;
}
if($annotraise==''){
    $annotraise=0;
}

/////////////////////////////////////////////////////////////////////////////
// Write out a template main.tex file that includes the score just outputted.
  if($font == 'times' || $font == 'palatino') {
    $setmainfont = '';
    $usefont = "\\usepackage{{$font}}\n\\usepackage[T1]{fontenc}";
  } elseif ($font == 'LinLibertineO') {
    $setmainfont = '';
    $usefont = "\\usepackage{libertine}";
  } else {
    $setmainfont = "\\setmainfont{{$font}}";
    $usefont = '';
  }
  $dred = $red/255;
  $dgreen = $green/255;
  $dblue = $blue/255;
  if($colorlines) {
    $coloredlines = "\\grecoloredlines{{$red}}{{$green}}{{$blue}}\n\\def\\greabovelinestextstyle#1{\\color{rubrum}\\small \\textit{#1}}";
  } else {
    $coloredlines = "";
  }
  if($colorsym) {
    $rubrumsym = "\\def\\rubrum{\\color{rubrum}}";
  } else {
    $rubrumsym = "\\def\\rubrum{}";
  }
  if($colortit) {
    $rubrumtit = "\\def\\rubrumtit{\\color{rubrum}}";
  } else {
    $rubrumtit = "\\def\\rubrumtit{}";
  }
  if($colorann) {
    $rubrumann = "\\def\\rubrumann{\\color{rubrum}}";
  } else {
    $rubrumann = "\\def\\rubrumann{}";
  }
  if($colorcom) {
    $rubrumcom = "\\def\\rubrumcom{\\color{rubrum}}";
  } else {
    $rubrumcom = "\\def\\rubrumcom{}";
  }
  if($colorlet) {
    $rubrumlet = "\\def\\rubrumlet{\\color{rubrum}}";
  } else {
    $rubrumlet = "\\def\\rubrumlet{}";
  }
  if($spacelinestext != "") {
    $grespacelinestext = "\\grechangedim{\\grespacelinestext}{{$spacelinestext}pt}";
  } else {
    $grespacelinestext = "";
  }
  if($spacebeneathtext != "") {
    $grespacebeneathtext = "\\grechangedim{\\grespacebeneathtext}{{$spacebeneathtext}pt}";
  } else {
    $grespacebeneathtext = "";
  }
  $handle = fopen($nametex, 'w');
  fwrite($handle, <<<EOF
\\documentclass[10pt]{article}
$papercmd
\\usepackage{graphicx,xcolor}
\\usepackage{calc}
\\definecolor{rubrum}{rgb}{{$dred},{$dgreen},{$dblue}}
$rubrumsym
$rubrumtit
$rubrumann
$rubrumcom
$rubrumlet
\\usepackage{gregoriotex}
\\usepackage[utf8]{luainputenc}
\\usepackage{fontspec}
$usefont
%\\textwidth {$width}mm
\\pagestyle{empty}
\\begin{document}
$setmainfont%
\\newfontfamily\\versiculum{Versiculum}
\\font\\initial={$initialfont} at {$initialsize}pt
\\gresetstafflinefactor{13}
$grespacelinestext
$grespacebeneathtext
$coloredlines

\\def\\greinitialformat#1{%
\\rubrumlet%
$initialFormat

\\tolerance=1000
\\pretolerance=300


\\def\\dualcomment#1#2{%
  {\\fontsize{12}{12}\\selectfont %
  \\setlength{\\parindent}{0pt}%
  \\vbox{\\textit{\\rubrumcom #1 \\hfill #2}}%
  \\vspace{0.25em}%
  \\relax%
}}
\\def\\*{\\raisebox{.5ex}[0pt][0pt]{%
        \\rubrum\\includegraphics[width=.9ex]{Asterisque}}%
}
\\let\\grestar\\*

\\def\\Abar{%
  {\\rubrum\\versiculum a}%
  \\relax%
}

\\def\\Rbar{%
  \\raisebox{-.4ex}{\\rubrum \\includegraphics[height=2ex]{Repons}}%
  \\relax%
}
\\catcode`\\℟=\\active \\def ℟#1{%
  \\raisebox{-.4ex}{\\rubrum \\includegraphics[height=2ex]{Repons}}%
  \\mbox{\\hspace{-.2ex}{\\rubrum#1}}%
}

\\def\\Vbar{%
  \\raisebox{-.4ex}{\\rubrum \\includegraphics[height=2ex]{Verset}}%
  \\relax%
}
\\catcode`\\℣=\\active \\def ℣#1{%
  \\raisebox{-.4ex}{\\rubrum \\includegraphics[height=2ex]{Verset}}%
  \\mbox{\\hspace{-.2ex}{\\rubrum#1}}%
}

\\catcode`\\✠=\\active \\def ✠{\\raisebox{-.1ex}[0pt][0pt]{%
        \\rubrum\\includegraphics[height=1.6ex]{Crux}}}

\\catcode`\\†=\\active \\def †{\\raisebox{-.4ex}[0pt][0pt]{%
        \\rubrum\\includegraphics[height=2.2ex]{Dague}}}
\\def\\gredagger{†}

\\def\\gretranslationformat#1{%
  \\fontsize{10}{10}\\selectfont\\it{#1}%
  \\relax %
}
\\def\\pdfliteral#1{%
  \\relax%
}
\\def\\UseAlternatePunctumCavum{%
\\gdef\\grepunctumcavumchar{\\gregoriofont\\char 75}%
\\gdef\\grelineapunctumcavumchar{\\gregoriofont\\char 76}%
\\gdef\\grepunctumcavumholechar{\\gregoriofont\\char 78}%
\\gdef\\grelineapunctumcavumholechar{\\gregoriofont\\char 80}%
\\relax %
}
\\gdef\\grelowchoralsignstyle#1{{\\fontsize{8}{8}\\selectfont #1}}
\\gdef\\grehighchoralsignstyle#1{{\\fontsize{8}{8}\\selectfont #1}}
%\\def\\greabovelinestextstyle#1{\\hspace*{-5pt}\\small\\textit{#1}}
% greinitialformat must be set before calling!
\\def\\greinitialformat#1{\\raisebox{{$initialraise}pt}{\\initial #1}}
\\newlength{\\annotwidth}
\\newlength{\\annottwowidth}
\\newlength{\\spacewidth}
\\newlength{\\initwidth}
\\newcommand{\\setinitialspacing}[1]{%
% 1 - initial, e.g., I
\\settowidth{\\annotwidth}{\\annot\\hspace{0.5pc}}
\\ifx\\annottwo\\undefined\\else%
\\settowidth{\\annottwowidth}{\\annottwo\\hspace{0.5pc}}
\\ifdim\\annottwowidth>\\annotwidth%
\\setlength{\\annotwidth}{\\annottwowidth}
\\fi
\\fi
\\settowidth{\\initwidth}{\\greinitialformat{#1}}
\\settowidth{\\spacewidth}{\\greinitialformat{#1}\\hspace{1pc}}
\\ifdim\\spacewidth<\\annotwidth%
\\setlength{\\spacewidth}{\\annotwidth}
\\fi
\\addtolength{\\spacewidth}{-\\initwidth}
\\setlength{\\spacewidth}{0.5\\spacewidth}
%
\\GreSetSpaceBeforeInitial{\\spacewidth}
\\GreSetSpaceAfterInitial{\\spacewidth}
%
\\gresetfirstannotation{\\annot}
\\ifx\\annottwo\\undefined\\else%
\\gresetsecondannotation{\\annottwo}
\\fi
}
$includeScores
\\end{document}
EOF
);
/////////////////////////////////////////////////////////////////////////

// Run lualatex on it.
  exec("export HOME=tmp && export TEXMFCNF=.: && export TEXINPUTS=lib: && lualatex -output-directory=tmp -interaction=nonstopmode $nametexS 2>&1", $lualatexOutput, $lualatexRetVal);
  if($lualatexRetVal){
    $result = array("file" => $nametex,
      "error" => implode("\n",$gregOutput) . "\n\n" . implode("\n",$lualatexOutput));
    header("Content-type: application/json");
    echo json_encode($result);
    return;
  }
// Copy the pdf into another directory, or upload to an FTP site.
  if($croppdf) {
    exec("pdfcrop '$namepdf' '$namepdf' 2>&1", $croppdfOutput, $croppdfRetVal);
    if($croppdfRetVal){
      $result = array("error" => implode("\n",$croppdfOutput));
      header("Content-type: application/json");
      echo json_encode($result);
      return;
    }
  }
function deleteOlderFilesIn($dir,$cutoff,$delIfEmpty) {
  $entries = scandir($dir);
  $count = 2;
  foreach($entries as $v) {
    $fn = "$dir/$v";
    if(is_dir($fn)) {
      if(preg_match('/^\./',$v)) {
        continue;
      }
      deleteOlderFilesIn("$dir/$v",$cutoff,true);
      $count = 0;
      continue;
    }
    $stat = stat($fn);
    if($stat['mtime'] < $cutoff) {
      unlink($fn);
      ++$count;
    }
  }
  if($delIfEmpty && $count == count($entries)) {
    rmdir($dir);
  }
}
  $cutoff = new DateTime(null, new DateTimeZone('Europe/Paris'));
  $cutoff->modify('-1 hour');
  $cutoff = $cutoff->getTimestamp();
  deleteOlderFilesIn($dir,$cutoff,false);
  deleteOlderFilesIn('tmp/',$cutoff,false);

  if($format=='eps'){
    //exec("gs -q -dNOPAUSE -dBATCH -dSAFER -sDEVICE=epswrite -r600 -sOutputFile=$finalpdfS $namepdf");
    exec("pdftops -eps $namepdf $finalpdfS");
  } else {
      if($namepdf == $finalpdf) {
        rename($namepdf,"$namepdf.tmp.pdf");
        $namepdf .= '.tmp.pdf';
    }
    //Instead of just renaming it, let's subset the fonts:
    exec("gs -q -dNOPAUSE -dBATCH -dSAFER -sDEVICE=pdfwrite -dCompatibilityLevel=1.3 -dEmbedAllFonts=true -dSubsetFonts=true -sOutputFile=$finalpdfS $namepdf");
  }
  if($format=='pdf' || $format=='eps'){
    //passthru("gs -q -dNOPAUSE -dBATCH -dSAFER -sDEVICE=pdfwrite -dCompatibilityLevel=1.3 -dEmbedAllFonts=true -dSubsetFonts=true -sOutputFile=- $finalpdfS");
    header('HTTP/1.1 301 Moved Permanently');
    header("Location: $finalpdf");
    exit();
//  } else if($format=='png') {
//    header("Content-type: $fmtmime");
//    passthru("convert -density 480 $finalpdfS +append -resize 25% $format:-");
  } else if($format=='json') {
    $result = array("href" => $finalpdf);
    header("Content-type: application/json");
    echo json_encode($result);
  }
}
?>
