<?php 
# ------------------ BEGIN LICENSE BLOCK ------------------
#	  ___________________________________________________
#    |													|
#    |					PHP GRAPH	    ____			|
#    |								   |	|			|
#    |						  ____	   |	|			|
#    |				 /\		 |	  |	   |	|			|
#    |			   /   \	 |	  |	   |	|			|
#    |		/\	 /		\	 |	  |____|	|			|
#    |	  /   \/		 \	 |	  |	   |	|			|
#    |	/				  \	 |	  |	   |	|			|
#    |/____________________\_|____|____|____|___________|
#
# @update     2013-12-17
# @copyright  2013 Cyril MAGUIRE
# @licence    http://www.cecill.info/licences/Licence_CeCILL_V2.1-fr.txt CONTRAT DE LICENCE DE LOGICIEL LIBRE CeCILL version 2.1
# @link       http://jerrywham.github.io/phpGraph/
# @version    1.0
#
# ------------------- END LICENSE BLOCK -------------------
class phpGraph {

	public $options = array(
		'responsive' => true,
		'width' => null,// (int) width of grid
		'height' => null,// (int) height of grid
		'paddingTop' => 10,// (int)
		'type' => 'line',// (string) bar or disk
		'steps' => null,// (int) 2 graduations on y-axis are separated by $steps units. "steps" is automatically calculated but we can set the value with integer
		'filled' => true,// (bool) to fill lines/histograms/disks
		'tooltips' => false,// (bool) to show tooltips
		'circles' => true,// (bool) to show circles on graph (lines or histograms)
		'stroke' => '#3cc5f1',// (string) color of lines by default. Use an array to personalize each line
		'background' => "#ffffff",// (string) color of grid background. Don't use short notation (#fff) because of $this->__genColor();
		'gradient' => null,// (array) 2 colors from left to right
		'titleHeight' => 0,// (int) Height of main title
		'tooltipLegend' => '',// (string or array) Text display in tooltip with y value. Each text can be personalized using an array.
		'legends' => '',// (string or array or bool) General legend for each line/histogram/disk displaying under diagram
		'title' => null,// (string) Main title. Title wil be displaying in a tooltip too.
		'radius' => 100,// (int) Radius of pie
		'diskLegends' => false,// (bool) to display legends around a pie
		'diskLegendsType' => 'label',// (string) data, pourcent or label to display around a pie as legend
	);
	
	public $colors = array();

	/**
	 * Constructor
	 *
	 * @param	$width integer Width of grid
	 * @param	$height integer Height of grid
	 * @param   $options array Options
	 * @return	stdio
	 *
	 * @author	Cyril MAGUIRE
	 **/
	public function __construct($width=600,$height=300,$options=array()) {
		if (!empty($options)) {
			$this->options = $options;
		}
		if (!empty($width)) {
			$this->options['width'] = $width;
		}
		if (!empty($height)) {
			$this->options['height'] = $height;
		}
		if (is_string($this->options['stroke'])) {
			$this->options['stroke'] = array(0=>$this->options['stroke']);
		}
		if (is_string($this->options['type'])) {
			$this->options['type'] = array(0=>$this->options['type']);
		}
	}

	/**
	 * Main function
	 * @param $data array Uni or bidimensionnal array
	 * @param $option array Array of options
	 * @return string SVG 
	 *
	 * @author Cyril MAGUIRE
	 */
	public function draw($data,$options=array()) {
		$return = '';
		$options = array_merge($this->options,$options);

		extract($options);

		if (isset($title)) {
			$options['titleHeight'] = $titleHeight = 40;
		}

		$HEIGHT = $height+$titleHeight+$paddingTop;

		$heightLegends = 0;
		if (isset($legends) && !empty($legends)) {
			$heightLegends = count($legends)*30+2*$paddingTop;
		}

		$pie = '';

		if ($type != 'pie') {
			$arrayOfMin = $arrayOfMax = $arrayOfLenght = $labels = array();
			//For each diagrams with several lines/histograms
			foreach ($data as $line => $datas) {
				if (is_array($datas)) {
					$arrayOfMin[] = min($datas);
					$arrayOfMax[] = max($datas);
					$arrayOfLenght[] = count($datas);
					$labels = array_merge(array_keys($datas),$labels);
					$multi = true;
				} else {
					$multi = false;
				}
			}
			if ($multi == true) {
				$labels = array_unique($labels);
				$min = min($arrayOfMin);
				$max = max($arrayOfMax);
				$lenght = max($arrayOfLenght);

			} else {
				$labels = array_keys($data);
				$lenght = count($data);
				$min = min($data);
				$max = max($data);
			}
			if (!is_float($max)) {
				$l = strlen(abs($max))-1;
				if ($l == 0) {$l = 1;}
				$M =  ceil($max/($l*10))*($l*10);
				$steps = $l*10;
			} else {
				$abs = abs($max);
				$decimal = substr($abs,strpos($abs, '.')+1);
				$int = substr($abs, 0, strpos($abs, '.'));
				$l = strlen($decimal)-1;
				if ($l == 0) {$l = 1;}
				$M =  $int.'.'.ceil($decimal/($l*10))*($l*10);
				$steps = '0.'.$l*10;
			}
			if (isset($options['steps']) && is_int($steps) ) {
				$steps = $options['steps'];
			}
			$stepX = $width / ($lenght - 1);
			$unitY = ($height/abs(($max+$steps)-$min));
			$gridV = $gridH = '';
			$x = $y = '';

			//Size of canevas will be bigger than grid size to display legends
			if ($responsive == true) {
				$return .= "\n".'<svg xmlns="http://www.w3.org/2000/svg" version="1.1" xml:lang="fr" xmlns:xlink="http://www.w3/org/1999/xlink" class="graph" width="70%" height="70%" viewBox="0 0 '.($lenght*$stepX+$stepX).' '.($HEIGHT+$heightLegends+$titleHeight+2*$paddingTop).'">'."\n";
			} else {
				$return .= "\n".'<svg xmlns="http://www.w3.org/2000/svg" version="1.1" xml:lang="fr" xmlns:xlink="http://www.w3/org/1999/xlink" class="graph" width="'.($lenght*$stepX+$stepX).'" height="'.($HEIGHT+$heightLegends+$titleHeight+2*$paddingTop).'" viewBox="0 0 '.($lenght*$stepX+$stepX).' '.($HEIGHT+$heightLegends+$titleHeight+2*$paddingTop).' ">'."\n";
			}

			if (is_array($gradient)) {
				$id = 'BackgroundGradient'.rand();
				$return .= "\n\t".'<defs>';
				$return .= "\n\t\t".'<linearGradient id="'.$id.'">';
				$return .= "\n\t\t\t".'<stop offset="5%" stop-color="'.$gradient[0].'" />';
				$return .= "\n\t\t\t".'<stop offset="95%" stop-color="'.$gradient[1].'" />';
				$return .= "\n\t\t".'</linearGradient>';
				$return .= "\n\t".'</defs>'."\n";
				$background = 'url(#'.$id.')';
			}
			//Grid is beginning at 50 units from the left
			$return .= "\t".'<rect x="50" y="'.($paddingTop+$titleHeight).'" width="'.$width.'" height="'.$height.'" class="graph-stroke" fill="'.$background.'" fill-opacity="1"/>'."\n";
			if (isset($title)) {
				$return .= "\t".'<title class="graph-tooltip">'.$title.'</title>'."\n";
				$return .= "\t".'<text x="250" y="40" text-anchor="start" class="graph-title">'.$title.'</text>'."\n";
			}
			//Legends x axis
			$x .= "\t".'<g class="graph-x">'."\n";
			$i=0;
			foreach ($labels as $key => $label) {
				//We add a gap of 50 units 
				$x .= "\t\t".'<text x="'.($i*$stepX+50).'" y="'.($HEIGHT+2*$paddingTop).'" text-anchor="middle">'.$label.'</text>'."\n";
				//Vertical grid
				if ($i != 0 && $i != $lenght) {
					$gridV .= "\t\t".'<path d="M '.($i*$stepX+50).' '.($paddingTop+$titleHeight).' V '.($HEIGHT).'"/>'."\n" ;
				}
				$i++;
			}
			$x .= "\t".'</g>'."\n";

			//Legendes y axis
			$y .= "\t".'<g class="graph-y">'."\n";
			if ($min>0 || ($min<0 && $max<0)) {
				$min = 0;
			}
			
			for ($i=$min; $i <= ($max+$steps); $i+=$steps) {
	 			//1 graduation every $steps units
	 			if ($min<0) {
	 				$stepY = $HEIGHT + $unitY*($min-$i);
	 			} else {
	 				$stepY = $HEIGHT - ($unitY*$i);
	 			}
	 				
	 			$y .= "\t\t".'<text x="40" y="'.$stepY.'" text-anchor="end" baseline-shift="-1ex" dominant-baseline="middle">'.($i).'</text>'."\n";

				//Horizontal grid
				$gridH .= "\t\t".'<path d="M 50 '.$stepY.' H '.($width+50).'"/>'."\n" ;
			}
			$y .= "\t".'</g>'."\n";

			//Grid
			$return .= "\t".'<g class="graph-grid">'."\n";
			$return .= $gridH."\n"; 
			$return .= $gridV; 
			$return .= "\t".'</g>'."\n";

			$return .= $x;
			$return .= $y;
			if (!$multi) {
				$options['stroke'] = is_array($stroke) ? $stroke[0] : $stroke;
				switch ($type) {
					case 'line':
						$return .= $this->__drawLine($data,$height,$HEIGHT,$stepX,$unitY,$lenght,$min,$max,$options);
						break;
					case 'bar':
						$return .= $this->__drawBar($data,$height,$HEIGHT,$stepX,$unitY,$lenght,$min,$max,$options);
						break;
					case 'pie':
						if (is_array($stroke)) {
							$options['stroke'] = $stroke;
							$options['fill'] = $stroke;
						}
						if (is_array($legends)) {
							$options['legends'] = $legends;
						}
						$pie .= $this->__drawDisk($data,$options);
						$pie .= "\n".'</svg>'."\n";
						break;
					default:
						$return .= $this->__drawLine($data,$height,$HEIGHT,$stepX,$unitY,$lenght,$min,$max,$options);
						break;
				}
			} else {
				foreach ($data as $line => $datas) {
					if (!isset($type[$line]) && !is_string($type)) {
						$type[$line] = 'line';
					}
					if (is_string($options['type'])) {
						$type = array();
						$type[$line] = $options['type'];
					}
					if (!isset($tooltipLegend[$line])) {
						$options['tooltipLegend'] = '';
					} else {
						$options['tooltipLegend'] = $tooltipLegend[$line];
					}
					if (!isset($stroke[$line])) {
						$stroke[$line] = $this->__genColor();
					}
					$options['stroke'] = $stroke[$line];
					$options['fill'] = $stroke[$line];
					
					switch ($type[$line]) {
						case 'line':
							$return .= $this->__drawLine($datas,$height,$HEIGHT,$stepX,$unitY,$lenght,$min,$max,$options);
							break;
						case 'bar':
							$return .= $this->__drawBar($datas,$height,$HEIGHT,$stepX,$unitY,$lenght,$min,$max,$options);
							break;
						case 'pie':
							$options['multi'] = $multi;
							if (is_array($stroke)) {
								$options['stroke'] = $stroke;
								$options['fill'] = $stroke;
							}
							if (is_array($legends)) {
								$options['legends'] = $legends;
							}
							$pie .= $this->__drawDisk($datas,$options);
							$pie .= "\n".'</svg>'."\n";
							break;
						default:
							$return .= $this->__drawLine($datas,$height,$HEIGHT,$stepX,$unitY,$lenght,$min,$max,$options);
							break;
					}
				}
			}
			if (isset($legends) && !empty($legends)) {
				$leg = "\t".'<g class="graph-legends">';
				if (!is_array($legends)) {
					$legends = array(0 => $legends);
				}
				foreach ($legends as $key => $value) {
					if ($type[$key] != 'pie') {
						if (is_array($stroke) && isset($stroke[$key])) {
							$leg .= "\n\t\t".'<rect x="50" y="'.($HEIGHT+30+$key*(2*$paddingTop)).'" width="10" height="10" fill="'.$stroke[$key].'" class="graph-legend-stroke"/>';
						} else {
							$leg .= "\n\t\t".'<rect x="50" y="'.($HEIGHT+30+$key*(2*$paddingTop)).'" width="10" height="10" fill="'.$stroke.'" class="graph-legend-stroke"/>';
						}
						$leg .= "\n\t\t".'<text x="70" y="'.($HEIGHT+40+$key*(2*$paddingTop)).'" text-anchor="start" class="graph-legend">'.$value.'</text>';
					}
				}
				$leg .= "\n\t".'</g>';

			} else {
				$leg = '';
			}
			$return .= $leg;
			$return .= "\n".'</svg>'."\n";
			$return .= $pie;
		} else {
			$options['tooltipLegend'] = array();
			if (isset($tooltipLegend) && !is_array($tooltipLegend)) {
				foreach ($data as $key => $value) {
					$options['tooltipLegend'][] = $tooltipLegend;
				}
			}
			if (isset($tooltipLegend) && is_array($tooltipLegend)) {
				$options['tooltipLegend'] = $tooltipLegend;
			}
			$options['stroke'] = array();
			if (isset($stroke) && !is_array($stroke)) {
				foreach ($data as $key => $value) {
					$options['stroke'][] = $stroke;
				}
			}
			if (isset($stroke) && is_array($stroke)) {
				$options['stroke'] = $stroke;
			}
			foreach ($data as $line => $datas) {
				if (is_array($datas)) {
					if (is_array($stroke)) {
						$options['stroke'] = $stroke;
						$options['fill'] = $stroke;
					}
					if (is_array($legends)) {
						$options['legends'] = $legends;
					}
					$return .= $this->__drawDisk($datas,$options);
					$return .= "\n".'</svg>'."\n";
					$multi = true;
				} else {
					$multi = false;
				}
			}
			if (!$multi) {
				if (is_array($stroke)) {
					$options['stroke'] = $stroke;
					$options['fill'] = $stroke;
				}
				if (is_array($legends)) {
					$options['legends'] = $legends;
				}
				$return .= $this->__drawDisk($data,$options);
				$return .= "\n".'</svg>'."\n";
			}

		}

		$this->colors = array();
		return $return;
	}

	/**
	 * To draw lines
	 * @param $data array Unidimensionnal array
	 * @param $height integer Height of grid
	 * @param $HEIGHT integer Height of grid + title + padding top
	 * @param $stepX integer Unit of x-axis
	 * @param $unitY integer Unit of y-axis
	 * @param $lenght integer Size of data array
	 * @param $min integer Minimum value of data
 	 * @param $max integer Maximum value of data
	 * @param $options array Options
	 * @return string Path of lines (with options)
	 *
	 * @author Cyril MAGUIRE
	 */
	public function __drawLine($data,$height,$HEIGHT,$stepX,$unitY,$lenght,$min,$max,$options) {
		$return = '';

		extract($options);

		$this->colors[] = $options['stroke'];

		//Ligne
		$i = 0;
		$c = '';
		$t = '';
		$path = "\t\t".'<path d="';
		foreach ($data as $label => $value) {
			
			//$min<0 
			$coordonnees1 = ($i * $stepX + 50).' '.($HEIGHT + $unitY*($min-$value));
			$coordonnees2 = 'x="'.($i * $stepX + 50).'" y="'.($HEIGHT + $unitY*($min-$value)).'"';
			//$min>=0
			$coordonnees3 = ($i * $stepX + 50).' '.($HEIGHT + $unitY*($min-$value) - $value);
			$coordonnees4 = 'x="'.($i * $stepX + 50).'" y="'.($HEIGHT + $unitY*($min-$value)).'"';
			$coordonnees5 = 'x="'.($i * $stepX + 50).'" y="'.($HEIGHT + $unitY*($min-$value) - $value).'"';
			//$min == $value
			$coordonnees6 = ($i * $stepX + 50).' '.($HEIGHT + $unitY*($min-$value) - $value*$unitY);
			$coordonnees7 = 'x="'.($i * $stepX + 50).'" y="'.($HEIGHT + $unitY*($min-$value) - $value*$unitY).'"';
			//Tooltips
			if($tooltips == true) {
				$c .= "\n\t\t".'<g class="graph-active">';
			}
			//Ligne
			if ($value != $max) {
				if ($value == $min) {
					if ($i == 0) {
						if ($min<=0) {
							$path .= 'M '.$coordonnees1.' L';
							//Tooltips and circles
							$c .= "\n\t\t\t".'<circle c'.str_replace('y="', 'cy="', $coordonnees2).' r="3" stroke="'.$stroke.'" class="graph-point-active"/>';
						} else {
							$path .= 'M '.$coordonnees6.' L';
							//Tooltips and circles
							$c .= "\n\t\t\t".'<circle c'.str_replace('y="', 'cy="', $coordonnees7).' r="3" stroke="'.$stroke.'" class="graph-point-active"/>';
						}
					} else {
						if ($min<=0) {
							$path .= "\n\t\t\t\t".$coordonnees1;
							//Tooltips and circles
							$c .= "\n\t\t\t".'<circle c'.str_replace('y="', 'cy="', $coordonnees2).' r="3" stroke="'.$stroke.'" class="graph-point-active"/>';
						} else {
							$path .= "\n\t\t\t\t".$coordonnees3;
							//Tooltips and circles
							$c .= "\n\t\t\t".'<circle c'.str_replace('y="', 'cy="', $coordonnees4).' r="3" stroke="'.$stroke.'" class="graph-point-active"/>';
						}
					}
				} else {
					if ($i == 0) {
						if ($min<=0) {
							$path .= 'M '.$coordonnees1.' L';
							//Tooltips and circles
							$c .= "\n\t\t\t".'<circle c'.str_replace('y="', 'cy="', $coordonnees2).' r="3" stroke="'.$stroke.'" class="graph-point-active"/>';
						} else {
							$path .= 'M '.$coordonnees3.' L';
							//Tooltips and circles
							$c .= "\n\t\t\t".'<circle c'.str_replace('y="', 'cy="', $coordonnees4).' r="3" stroke="'.$stroke.'" class="graph-point-active"/>';
						}
					} else {
						if ($i != $lenght-1) {
							if ($min<=0) {
								$path .= "\n\t\t\t\t".$coordonnees1;
								//Tooltips and circles
								$c .= "\n\t\t\t".'<circle c'.str_replace('y="', 'cy="', $coordonnees2).' r="3" stroke="'.$stroke.'" class="graph-point-active"/>';
							} else {
								$path .= "\n\t\t\t\t".$coordonnees3;
								//Tooltips and circles
								$c .= "\n\t\t\t".'<circle c'.str_replace('y="', 'cy="', $coordonnees5).' r="3" stroke="'.$stroke.'" class="graph-point-active"/>';
							}
						} else {
							if ($min<=0) {
								$path .= "\n\t\t\t\t".$coordonnees1;
								//Tooltips and circles
								$c .= "\n\t\t\t".'<circle c'.str_replace('y="', 'cy="', $coordonnees2).' r="3" stroke="'.$stroke.'" class="graph-point-active"/>';
							} else {
								$path .= "\n\t\t\t\t".$coordonnees3;
								//Tooltips and circles
								$c .= "\n\t\t\t".'<circle c'.str_replace('y="', 'cy="', $coordonnees4).' r="3" stroke="'.$stroke.'" class="graph-point-active"/>';
							}
						}
					}
				}
			} else {
				//Line
				if ($i == 0) {
					$path .= 'M '.($i * $stepX + 50).' '.($titleHeight + 2 * $paddingTop).' L';
					//Tooltips and circles
					$c .= "\n\t\t\t".'<circle cx="'.($i * $stepX + 50).'" cy="'.($titleHeight + 2 * $paddingTop).'" r="3" stroke="'.$stroke.'" class="graph-point-active"/>';
				} else {
					//$path .= "\n\t\t\t\t".($i * $stepX + 50).' '.($titleHeight + 2 * $paddingTop);
					$path .= "\n\t\t\t\t".$coordonnees1;
					//Tooltips and circles
					$c .= "\n\t\t\t".'<circle c'.str_replace('y="', 'cy="', $coordonnees2).' r="3" stroke="'.$stroke.'" class="graph-point-active"/>';
				}
				
			}
			$i++;
			//End tooltips
			if($tooltips == true) {
				$c .= "\n\t\t\t".'<title class="graph-tooltip">'.(is_array($tooltipLegend) ? $tooltipLegend[$i] : $tooltipLegend).$value.'</title>'."\n\t\t".'</g>';
			}
		}
		//End of line
		$pathLine = '" class="graph-line" stroke="'.$stroke.'" fill="#fff" fill-opacity="0"/>'."\n";
		//Filling
		if ($filled === true) {
			if ($min<=0) {
				$path .= "\n\t\t\t\t".(($i - 1) * $stepX + 50).' '.($HEIGHT + ($unitY)*($min-$value) + ($unitY * $value)).' 50 '.($HEIGHT + ($unitY)*($min-$value) + ($unitY * $value))."\n\t\t\t\t";
			} else {
				$path .= "\n\t\t\t\t".(($i - 1) * $stepX + 50).' '.$HEIGHT.' 50 '.$HEIGHT."\n\t\t\t\t";
			}
			
			$return .= $path.'" class="graph-fill" fill="'.$stroke.'" fill-opacity="0.5"/>'."\n";
		}
		//Display line
		$return .= $path.$pathLine;
		
		if($circles == true) {
			$return .= "\t".'<g class="graph-point">';
			$return .= $c;
			$return .= "\n\t".'</g>'."\n";
		}
		return $return;
	}

	/**
	 * To draw histograms
	 * @param $data array Unidimensionnal array
	 * @param $height integer Height of grid
	 * @param $HEIGHT integer Height of grid + title + padding top
	 * @param $stepX integer Unit of x-axis
	 * @param $unitY integer Unit of y-axis
	 * @param $lenght integer Size of data array
	 * @param $min integer Minimum value of data
 	 * @param $max integer Maximum value of data
	 * @param $options array Options
	 * @return string Path of lines (with options)
	 *
	 * @author Cyril MAGUIRE
	 */
	public function __drawBar($data,$height,$HEIGHT,$stepX,$unitY,$lenght,$min,$max,$options) {
		$return = '';

		extract($options);
		
		$this->colors[] = $options['stroke'];

		//Bar
		$bar = '';
		$i = 0;
		$c = '';
		$t = '';
		foreach ($data as $label => $value) {

			//Tooltips and circles
			if($tooltips == true) {
				$c .= "\n\t\t".'<g class="graph-active">';
			}

			$stepY = $value*$unitY;

			//$min>=0
			$coordonnees1 = 'x="'.($i * $stepX + 50).'" y="'.($HEIGHT + $unitY*($min-$value)).'"';
			//On recule d'un demi pas pour que la valeur de x soit au milieu de la barre de diagramme
			$coordonnees2 = 'x="'.($i * $stepX + 50 - $stepX/2).'" y="'.($HEIGHT - $stepY).'"';
			//$min<0
			$coordonnees3 = 'x="'.($i * $stepX + 50).'" y="'.($HEIGHT + $unitY*($min-$value)).'"';
			$coordonnees4 = 'x="'.($i * $stepX + 50 - $stepX/2).'" y="'.($HEIGHT + $unitY*($min-$value)).'"';
			//$min<0 et $value<0
			$coordonnees5 = 'x="'.($i * $stepX + 50 - $stepX/2).'" y="'.($HEIGHT + $unitY*($min-$value)).'"';
			$coordonnees6 = 'x="'.($i * $stepX + 50).'" y="'.($HEIGHT + $unitY*($min-$value)).'"';
			//$min>=0 et $value == $max
			$coordonnees7 = 'x="'.($i * $stepX + 50 - $stepX/2).'" y="'.($HEIGHT - $stepY).'"';
			$coordonnees8 = 'x="'.($i * $stepX + 50).'" y="'.($paddingTop + $titleHeight).'"';
			//$value == 0
			$coordonnees9 = 'x="50" y="'.($HEIGHT + $unitY*$min).'"';
			if ($value == 0) {
				$stepY = 1;
			}
			//Diagramme
			//On est sur la première valeur, on divise la largeur de la barre en deux
			if ($i == 0) {
				if ($value == $max) {
					if ($min>=0) {
						$bar .= "\n\t".'<rect '.$coordonnees8.' width="'.($stepX/2).'" height="'.$height.'" class="graph-bar" stroke="'.$stroke.'" fill="#fff" fill-opacity="0"/>';
					} else {
						$bar .= "\n\t".'<rect '.$coordonnees8.' width="'.($stepX/2).'" height="'.$height.'" class="graph-bar" stroke="'.$stroke.'" fill="#fff" fill-opacity="0"/>';
					}
					
					$c .= "\n\t\t\t".'<circle c'.str_replace('y="', 'cy="', $coordonnees8).' r="3" stroke="'.$stroke.'" class="graph-point-active"/>';
					
				} else {
					if ($min>=0) {
						$bar .= "\n\t".'<rect '.$coordonnees1.' width="'.($stepX/2).'" height="'.$stepY.'" class="graph-bar" stroke="'.$stroke.'" fill="#fff" fill-opacity="0"/>';
						
						$c .= "\n\t\t\t".'<circle c'.str_replace('y="', 'cy="', $coordonnees1).' r="3" stroke="'.$stroke.'" class="graph-point-active"/>';
						
					} else {
						if ($value == $min) {
							$bar .= "\n\t".'<rect '.$coordonnees6.' width="'.($stepX/2).'" height="'.-$stepY.'" class="graph-bar" stroke="'.$stroke.'" fill="#fff" fill-opacity="0"/>';
						} else {
							if ($value == 0) {
								$bar .= "\n\t".'<rect '.$coordonnees9.' width="'.($stepX/2).'" height="1" class="graph-bar" stroke="'.$stroke.'" fill="#fff" fill-opacity="0"/>';
							} else {
								$bar .= "\n\t".'<rect '.$coordonnees3.' width="'.($stepX/2).'" height="'.$stepY.'" class="graph-bar" stroke="'.$stroke.'" fill="#fff" fill-opacity="0"/>';
							}
						}
						
						$c .= "\n\t\t\t".'<circle c'.str_replace('y="', 'cy="', $coordonnees3).' r="3" stroke="'.$stroke.'" class="graph-point-active"/>';
						
					}
					
				}
			} else {
				if ($value == $max) {
					if ($min>=0) {
						//Si on n'est pas sur la dernière valeur
						if ($i != $lenght-1) {
							$bar .= "\n\t".'<rect '.$coordonnees2.' width="'.$stepX.'" height="'.$stepY.'" class="graph-bar" stroke="'.$stroke.'" fill="#fff" fill-opacity="0"/>';
							
						} else {
							$bar .= "\n\t".'<rect '.$coordonnees7.' width="'.($stepX/2).'" height="'.$height.'" class="graph-bar" stroke="'.$stroke.'" fill="#fff" fill-opacity="0"/>';
						}
						$c .= "\n\t\t\t".'<circle c'.str_replace('y="', 'cy="', $coordonnees1).' r="3" stroke="'.$stroke.'" class="graph-point-active"/>';
					} else {
						if ($value >= 0) {
							//Si on n'est pas sur la dernière valeur
							if ($i != $lenght-1) {
								$bar .= "\n\t".'<rect '.$coordonnees4.' width="'.$stepX.'" height="'.$stepY.'" class="graph-bar" stroke="'.$stroke.'" fill="#fff" fill-opacity="0"/>';
							} else {
								$bar .= "\n\t".'<rect '.$coordonnees4.' width="'.($stepX/2).'" height="'.$stepY.'" class="graph-bar" stroke="'.$stroke.'" fill="#fff" fill-opacity="0"/>';
							}
						} else {
							//Si on n'est pas sur la dernière valeur
							if ($i != $lenght-1) {
								$bar .= "\n\t".'<rect '.$coordonnees5.' width="'.$stepX.'" height="'.-$stepY.'" class="graph-bar" stroke="'.$stroke.'" fill="#fff" fill-opacity="0"/>';
							} else {
								$bar .= "\n\t".'<rect '.$coordonnees5.' width="'.($stepX/2).'" height="'.-$stepY.'" class="graph-bar" stroke="'.$stroke.'" fill="#fff" fill-opacity="0"/>';
							}
						}
						
						$c .= "\n\t\t\t".'<circle c'.str_replace('y="', 'cy="', $coordonnees3).' r="3" stroke="'.$stroke.'" class="graph-point-active"/>';
						
					}
				}else {
					if ($min>=0) {
						//Si on n'est pas sur la dernière valeur
						if ($i != $lenght-1) {
							$bar .= "\n\t".'<rect '.$coordonnees2.' width="'.$stepX.'" height="'.$stepY.'" class="graph-bar" stroke="'.$stroke.'" fill="#fff" fill-opacity="0"/>';
						} else {
							$bar .= "\n\t".'<rect '.$coordonnees2.' width="'.($stepX/2).'" height="'.$stepY.'" class="graph-bar" stroke="'.$stroke.'" fill="#fff" fill-opacity="0"/>';
						}
						
						$c .= "\n\t\t\t".'<circle c'.str_replace('y="', 'cy="', $coordonnees1).' r="3" stroke="'.$stroke.'" class="graph-point-active"/>';
						
					} else {
						if ($value >= 0) {
							//Si on n'est pas sur la dernière valeur
							if ($i != $lenght-1) {
								$bar .= "\n\t".'<rect '.$coordonnees4.' width="'.$stepX.'" height="'.($stepY).'" class="graph-bar" stroke="'.$stroke.'" fill="#fff" fill-opacity="0"/>';
							} else {
								$bar .= "\n\t".'<rect '.$coordonnees4.' width="'.($stepX/2).'" height="'.$stepY.'" class="graph-bar" stroke="'.$stroke.'" fill="#fff" fill-opacity="0"/>';
							}
						} else {
							//Si on n'est pas sur la dernière valeur
							if ($i != $lenght-1) {
								$bar .= "\n\t".'<rect '.$coordonnees5.' width="'.$stepX.'" height="'.-$stepY.'" class="graph-bar" stroke="'.$stroke.'" fill="#fff" fill-opacity="0"/>';
							} else {
								$bar .= "\n\t".'<rect '.$coordonnees5.' width="'.($stepX/2).'" height="'.-$stepY.'" class="graph-bar" stroke="'.$stroke.'" fill="#fff" fill-opacity="0"/>';
							}
						}
						
						$c .= "\n\t\t\t".'<circle c'.str_replace('y="', 'cy="', $coordonnees3).' r="3" stroke="'.$stroke.'" class="graph-point-active"/>';
						
					}
				}
			}
			$i++;
			//End of tooltips
			if($tooltips == true) {
				$c .= '<title class="graph-tooltip">'.(is_array($tooltipLegend) ? $tooltipLegend[$i] : $tooltipLegend).$value.'</title>'."\n\t\t".'</g>';
			}
		}

		//Filling
		if ($filled === true) {
			$barFilled = str_replace(' class="graph-bar" stroke="'.$stroke.'" fill="#fff" fill-opacity="0"/>', ' class="graph-bar" fill="'.$stroke.'" fill-opacity="0.5"/>',$bar);
			$return .= $barFilled;
		}

		$return .= $bar;

		if($circles == true) {
			$return .= "\n\t".'<g class="graph-point">';
			$return .= $c;
			$return .= "\n\t".'</g>'."\n";
		}
		return $return;
	}

	/**
	 * Searches the array for a given value and returns the corresponding key if successful
	 * @param $needle mixed The searched value
	 * @param $haystack array The array
	 * 
	 * @author buddel (see comments on php man array_search function page)
	 */
	public function recursive_array_search($needle,$haystack) {
	    foreach($haystack as $key=>$value) {
	        $current_key=$key;
	        if($needle===$value OR (is_array($value) && $this->recursive_array_search($needle,$value) !== false)) {
	            return $current_key;
	        }
	    }
	    return false;
	}

	/**
	 * To draw pie diagrams
	 * @param $data array Unidimensionnal array
	 * @param $options array Options
	 * @return string Path of lines (with options)
	 *
	 * @author Cyril MAGUIRE
	 */
	public function __drawDisk($data,$options=array()) {

		$options = array_merge($this->options,$options);

		extract($options);

		$lenght = count($data);
		$max = max($data);

		$total = 0;
		foreach ($data as $label => $value) {
			if ($value < 0) {$value = 0;}
			$total += $value;
		}
		$deg = array();
		$i = 0;
		foreach ($data as $label => $value) {
			
			if ($value < 0) {$value = 0;}
			if ($total == 0) {
				$deg[] = array(
					'pourcent' => 0,
					'val' => $value,
					'label' => $label,
					'tooltipLegend' => (is_array($tooltipLegend) && isset($tooltipLegend[$i])) ? $tooltipLegend[$i] : (isset($tooltipLegend) ? $tooltipLegend : ''),
					'stroke' => (is_array($stroke) && isset($stroke[$i]))? $stroke[$i] : $this->__genColor(),
				);
			} else {
				$deg[] = array(
					'pourcent' => round(((($value * 100)/$total)/100),2),
					'val' => $value,
					'label' => $label,
					'tooltipLegend' => (is_array($tooltipLegend) && isset($tooltipLegend[$i])) ? $tooltipLegend[$i] : (isset($tooltipLegend) ? $tooltipLegend : ''),
					'stroke' => (is_array($stroke) && isset($stroke[$i]) ) ? $stroke[$i] : $this->__genColor(),
				);
			}
			$i++;
		}
		if (isset($legends)) {
			if (!is_array($legends) && !empty($legends) && !is_bool($legends)) {
				$legends = array( 
					'label' => $legends,
					'stroke' => (is_array($stroke) ) ? $stroke[0] : $this->__genColor()
				);
			} elseif (empty($legends)) {
				$notDisplayLegends = true;
			} elseif (is_bool($legends)) {
				$legends = array();
			}
			foreach ($deg as $k => $v) {
				if (!isset($legends[$k])) {
					$legends[$k] = array(
						'label' => $v['label'],
						'stroke' => (is_array($stroke) && isset($stroke[$k]) ) ? $stroke[$k] : $v['stroke']
					);
				}else {
					$legends[$k] = array(
						'label' => (isset($multi) ? $v['label'] : $legends[$k]),
						'stroke' => $v['stroke']
					);
				}
			}
		}
		$deg = array_reverse($deg);

		$heightLegends = 0;
		if (isset($legends) && !empty($legends)) {
			$heightLegends = count($legends)*30+2*$paddingTop;
		}

		$this->colors[] = $options['stroke'];

		$originX = (2*$radius+400)/2;
		$originY = 10+$titleHeight+2*$paddingTop;


		//Size of canevas will be bigger than grid size to display legends
		$return = "\n".'<svg width="'.(2*$radius+400).'" height="'.(2*$radius+100+$titleHeight+$paddingTop+$heightLegends).'" viewBox="0 0 '.(2*$radius+400).' '.(2*$radius+100+$titleHeight+$paddingTop+$heightLegends).'" xmlns="http://www.w3.org/2000/svg" version="1.1">'."\n";
		$return .= "\n\t".'<defs>';
		$return .= "\n\t\t".'<marker id="Triangle"';
		$return .= "\n\t\t\t".'viewBox="0 0 10 10" refX="0" refY="5"';
		$return .= "\n\t\t\t".'markerUnits="strokeWidth"';
		$return .= "\n\t\t\t".'markerWidth="4" markerHeight="3"';
		$return .= "\n\t\t\t".'fill="#a1a1a1" fill-opacity="0.7"';
		$return .= "\n\t\t\t".'orient="auto">';
		$return .= "\n\t\t\t".'<path d="M 0 0 L 10 5 L 0 10 z" />';
		$return .= "\n\t\t".'</marker>';
		if (is_array($gradient)) {
			$id = 'BackgroundGradient'.rand();
			$return .= "\n\t\t".'<linearGradient id="'.$id.'">';
			$return .= "\n\t\t\t".'<stop offset="5%" stop-color="'.$gradient[0].'" />';
			$return .= "\n\t\t\t".'<stop offset="95%" stop-color="'.$gradient[1].'" />';
			$return .= "\n\t\t".'</linearGradient>';
			$return .= "\n\t".'</defs>'."\n";
			$background = 'url(#'.$id.')';
			$return .= "\t".'<rect x="0" y="0" width="'.(2*$radius+400).'" height="'.(2*$radius+100+$titleHeight+$paddingTop+$heightLegends).'" class="graph-stroke" fill="'.$background.'" fill-opacity="1"/>'."\n";
		} else {
			$return .= "\n\t".'</defs>'."\n";
		}
		
  		if (isset($title)) {
			$return .= "\t".'<text x="'.($originX).'" y="'.$titleHeight.'" text-anchor="middle" class="graph-title">'.$title.'</text>'."\n";
		}

		$ox = $prevOriginX = $originX;
		$oy = $prevOriginY = $originY;
		$total = 1;

		$i = 0;
		while ($i <= $lenght-1) { 
			if ($deg[$i]['val'] != 0) {
				//Tooltips
				if($tooltips == true) {
					$return .= "\n\t\t".'<g class="graph-active">';
				}
				$color = $this->__genColor();
				$return .= "\n\t\t\t".'<circle cx="'.$originX.'" cy="'.($originY+2*$radius).'" r="'.$radius.'" fill="'.$color.'" class="graph-pie"/>'."\n\t\t\t";
				if(isset($legends) && !empty($legends)) {
					$tmp = $legends[$i]['label'];
					$legends[$this->recursive_array_search($deg[$i]['label'],$legends)]['label'] = $tmp;
					$legends[$i]['stroke'] = $color;
					$legends[$i]['label'] = $deg[$i]['label'];
				}

				$return .= "\n\t\t\t".'<path d=" M '.$originX.' '.($originY+2*$radius).' L '.$originX.' '.($originY+10).'" class="graph-line" stroke="darkgrey" stroke-opacity="0.5" stroke-dasharray="2,2,2" marker-end="url(#Triangle)"/>';

				$return .= "\n\t\t\t".'<text x="'.$originX.'" y="'.$originY.'" class="graph-legend" stroke="darkgrey" stroke-opacity="0.5">'.($diskLegendsType == 'label' ? (isset($legends[$i]['label']) ? $legends[$i]['label'] : $deg[$i]['label']) : ($diskLegendsType == 'pourcent' ? ($deg[$i]['pourcent']*100).'%' : $deg[$i]['val'])).'</text>'."\n\t\t\t";
				
				//End tooltips
				if($tooltips == true) {
					$return .= '<title class="graph-tooltip">'.$deg[$i]['tooltipLegend'].(isset($legends[$i]['label']) ? $legends[$i]['label'] : $deg[$i]['label']).' : '.$deg[$i]['val'].'</title>';
					$return .= "\n\t\t".'</g>';
				}
				$i = $deg[$i]['label'];
				break;
			}
			$i++;
		}
		$tmp = array(); 
		foreach($legends as &$ma) 
		    $tmp[] = &$ma['label']; 
		array_multisort($tmp, $legends); 

		foreach ($deg as $key => $value) {

				$total -= $value['pourcent'];

				$cos = cos((-90 + 360 * $total) * M_PI / 180)*$radius;
				$sin = sin((-90 + 360 * $total) * M_PI / 180)*$radius;

				$cosLeg = cos((-90 + 360 * $total) * M_PI / 180)*(2*$radius);
				$sinLeg = sin((-90 + 360 * $total) * M_PI / 180)*(2*$radius);

				//Tooltips
				if($tooltips == true && $key < ($lenght-1)) {
					$return .= "\n\t\t".'<g class="graph-active">';
				}
				
				if ($total >= 0 && $total <= 0.25 || $total == 1) {
					$arc = 0;
					$gap = ($radius/4);
					$gapTextX = ($radius/4) - 10;
					$gapTextY = ($radius/4) - 10;
				}
				if($total > 0.25 && $total <= 0.5) {
					$arc = 0;
					$gap = -($radius/4);
					$gapTextX = ($radius/8);
					$gapTextY = -($radius/4);
				}
				if($total > 0.5 && $total < 0.75) {
					$arc = 1;
					$gap = -($radius/4);
					$gapTextX = -($radius/8)-20;
					$gapTextY = ($radius/8)-20;
				} 
				if($total > 0.75 && $total < 1) {
					$arc = 1;
					$gap = ($radius/4);
					$gapTextX = -($radius/4);
					$gapTextY = ($radius/4)-10;
				}

				$return .= "\n\t\t\t".'<path d="M '.$originX.' '.($originY + $radius).'  A '.$radius.' '.$radius.'  0 '.$arc.' 1 '.($originX + $cos).' '.($originY + 2*$radius + $sin).' L '.$originX.' '.($originY+2*$radius).' z" fill="'.($key < ($lenght-1) ? $deg[ $key+1]['stroke'] : $legends[0]['stroke']).'" class="graph-pie"/>'."\n\t\t\t";

				if ($key < ($lenght-1) && $deg[$key+1]['val'] != 0 && $diskLegends == true && $deg[$key+1]['label'] != $i) {
					$return .= "\n\t\t\t".'<path d=" M '.($originX+$cos).' '.($originY+2*$radius + $sin).' L '.($originX + $cosLeg).' '.($originY + 2*$radius + $sinLeg + $gap).'" class="graph-line" stroke="darkgrey" stroke-opacity="0.5"  stroke-dasharray="2,2,2" marker-end="url(#Triangle)"/>';

					$return .= "\n\t\t\t".'<text x="'.($originX + $cosLeg + $gapTextX).'" y="'.($originY + 2*$radius + $sinLeg + $gapTextY).'" class="graph-legend" stroke="darkgrey" stroke-opacity="0.5">'.($diskLegendsType == 'label' ? (isset($legends[$lenght-$key-2]['label']) ? $legends[$lenght-$key-2]['label'] : $deg[$key+1]['label']) : ($diskLegendsType == 'pourcent' ? ($deg[$key+1]['pourcent']*100).'%' : $deg[$key+1]['val'])).'</text>'."\n\t\t\t";
				}
				//End tooltips
				if($tooltips == true && $key < ($lenght-1)) {
					$return .= '<title class="graph-tooltip">'.$deg[$key+1]['tooltipLegend'].(isset($legends[$lenght-$key-2]['label']) ? $legends[$lenght-$key-2]['label'] : $deg[$key+1]['label']).' : '.$deg[$key+1]['val'].'</title>'."\n\t\t".'</g>';
				}
		}

		if (isset($legends) && !empty($legends) && !isset($notDisplayLegends)) {
			$leg = "\t".'<g class="graph-legends">';
			foreach ($legends as $key => $value) {
				$leg .= "\n\t\t".'<rect x="50" y="'.(4*$radius+$titleHeight+$paddingTop+30+$key*(2*$paddingTop)).'" width="10" height="10" fill="'.((is_array($stroke) && isset($stroke[$key]) ) ? $stroke[$key] : $value['stroke']).'" class="graph-legend-stroke"/>';
				$leg .= "\n\t\t".'<text x="70" y="'.(4*$radius+$titleHeight+$paddingTop+40+$key*(2*$paddingTop)).'" text-anchor="start" class="graph-legend">'.$value['label'].'</text>';
			}
			$leg .= "\n\t".'</g>';

			$return .= $leg;
		}

		return $return;
	}
	/**
	 * To generate hexadecimal code for color
	 * @param null
	 * @return string hexadecimal code
	 *
	 * @author Cyril MAGUIRE
	 */
	public function __genColor() {
		$val = array("0","1","2","3","4","5","6","7","8","9","A","B","C","D","E","F");
		shuffle($val);
		$rand = array_rand($val,6);
		$hexa = '';
		foreach ($rand as $key => $keyOfVal) {
			$hexa .= $val[$keyOfVal];
		}
		if ('#'.$hexa == $this->options['background']) {
			return $this->__genColor();
		}
		if (!in_array($hexa, $this->colors)) {
			$this->colors[] = $hexa;
			return '#'.$hexa;
		} else {
			return $this->__genColor();
		}
	}
}
?>
