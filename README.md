phpGraph
========

Writing svg histogram with php

How to use ?
------------

First, data must be in an php array (one or two dimensions). 
Then, an instance of phpGraph class have to be called after an inclusion of phpGraph.php file in your project.
Then, the main method called draw is used, with or withour options. It's up to you.

Add phpGraph_style.css to header of your page for display graph.

    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
        <title>phpGraph</title>
        <link rel="stylesheet" type="text/css" href="phpGraph_style.css" media="all">
    </head>
    <body> (...)

By default, it's a line histogram which is displaying.

To resume :

    <?php 
    include(phpGraph.php);
  
    $data = array(
      'serie 1' => 10,
      'serie 2' => 38,
      'serie 3' => 23,
      'serie 4' => -15
     );
  
    $G = new phpGraph();
    
    echo $G->draw($data);
    ?>
  
Options available :
-------------------
  
     //All options available
    $options = array(
    'width' => null,// (int) width of grid
    'height' => null,// (int) height of grid
    'paddingTop' => 10,// (int)
    'type' => 'line',// (string) line, bar, pie, ring, stock or h-stock (todo curve)
    'steps' => null,// (int) 2 graduations on y-axis are separated by $steps units. "steps" is automatically calculated but we can set the value with integer. No effect on stock and h-stock charts
    'filled' => true,// (bool) to fill lines/histograms/disks
    'tooltips' => false,// (bool) to show tooltips
    'circles' => true,// (bool) to show circles on graph (lines or histograms). No effect on stock and h-stock charts
    'stroke' => '#3cc5f1',// (string) color of lines by default. Use an array to personalize each line
    'background' => "#ffffff",// (string) color of grid background. Don't use short notation (#fff) because of $this->__genColor();
    'opacity' => '0.5',// (float) between 0 and 1. No effect on stock and h-stock charts
    'gradient' => null,// (array) 2 colors from left to right
    'titleHeight' => 0,// (int) Height of main title
    'tooltipLegend' => null,// (string or array) Text display in tooltip with y value. Each text can be personalized using an array. No effect on stock and h-stock charts
    'legends' => null,// (string or array or bool) General legend for each line/histogram/disk displaying under diagram
    'title' => null,// (string) Main title. Title wil be displaying in a tooltip too.
    'radius' => 100,// (int) Radius of pie
    'diskLegends' => false,// (bool) to display legends around a pie
    'diskLegendsType' => 'label',// (string) data, pourcent or label to display around a pie as legend
    'diskLegendsLineColor' => 'darkgrey',// (string) color of lines which join pie to legends
    'responsive' => true,// (bool) to avoid svg to be responsive (dimensions fixed)
    'paddingLegendX' => 10,//We add 10 units in viewbox to display x legend correctly
    );
    
See [samples.php](http://www.ecyseo.net/?static8/phpgraph) for more details.

[Page of project on github](http://jerrywham.github.io/phpGraph/)

Licence :
---------
[CONTRAT DE LICENCE DE LOGICIEL LIBRE CeCILL version 2.1](http://www.cecill.info/licences/Licence_CeCILL_V2.1-fr.txt)

Change log :
---------
2015-01-29
  * [BUG] calcul of position of points for lines and bars
  * [BUG] position of legend for pies and rings
  * [BUG] put in cache
  
2015-01-28
  * [+] css file is not mandatory. Style is included in svg and can be modified via setCss() method
  * [+] new option : diskLegendsLineColor
  * [BUG] colors of legends
  * [BUG] position of lines between pie and labels