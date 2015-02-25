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
# @update     2015-02-11
# @copyright  2013-2015 Cyril MAGUIRE
# @licence    http://www.cecill.info/licences/Licence_CeCILL_V2.1-fr.txt CONTRAT DE LICENCE DE LOGICIEL LIBRE CeCILL version 2.1
# @link       http://jerrywham.github.io/phpGraph/
# @version    1.4
#
# ------------------- END LICENSE BLOCK -------------------
/**
 * @package    SIGesTH
 * @author     MAGUIRE Cyril <contact@ecyseo.net>
 * @copyright  2009-2015 Cyril MAGUIRE, <contact@ecyseo.net>
 * @license    Licensed under the CeCILL v2.1 license. http://www.cecill.info/licences.fr.html
 */
class phpGraph {

	# Basic css style
	protected $css = '
		.draw {
			width:70%;/*Adjust this value to resize svg automatically*/
			margin:auto;
		}
		svg {/*width and height of svg is 100% of dimension of its parent (draw class here)*/
			display: block;
			margin:auto;
			margin-bottom: 50px;
		}
		.graph-title {
			stroke-width:4;
			stroke:transparent;
			fill:#000033;
			font-size: 1.2em;
		}
		.graph-grid {
			stroke-width:1;
			stroke:#c4c4c4;
		}
		.graph-stroke {
			stroke-width:2;
			stroke:#424242;
		}
		.graph-legends {}
		.graph-legend {}
		.graph-legend-stroke {}
		.graph-line {
			stroke-linejoin:round;
			stroke-width:2;
		}
		.graph-fill {
			stroke-width:0;
		}
		.graph-bar {}
		.graph-point {
			stroke-width:1;
			fill:#fff;
			fill-opacity:1;
			stroke-opacity:1;
		}
		.graph-point-active:hover {
			stroke-width:5;
			transition-duration:.9s;
			cursor: pointer;
		}
		 title.graph-tooltip {
			background-color:#d6d6d6;
		}
		.graph-pie {
			cursor: pointer;
			stroke-width:1;
			stroke:#fff;
		}
		text {
			fill:#000;
		}
	';

	protected $options = array(
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
		'paddingLegendY' => 0,//Padding to display y legend correctly, if needed, for pie and ring
	);
	# authorized types
	protected $types = array('line','line','bar','pie','ring','stock','h-stock');

	private static $instance;
	
	//protected $colors = array();

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
		if (is_string($this->options['stroke']) && substr($this->options['stroke'], 0,1) == '#') {
			$this->options['stroke'] = array(0=>substr($this->options['stroke'],0,7));
		}
		if (is_string($this->options['type']) && in_array($this->options['type'], $this->types)) {
			$this->options['type'] = array(0=>$this->options['type']);
		}
	}

	/**
	 * Méthode qui se charger de créer le Singleton
	 *
	 * @return	objet			retourne une instance de la classe
	 * @author	Stephane F
	 **/
	public static function getInstance(){
		if (!isset(self::$instance)) {
			$class = __CLASS__;
			self::$instance = false;
			self::$instance = new $class();
		}
		return self::$instance;
	}

	/**
	 * To add your own style
	 * @param $css string your css
	 * @return string css
	 *
	 * @author Cyril MAGUIRE
	 */
	public function setCss($css) {
		if (is_string($css) && !empty($css)) {
			$this->css .= $css;
		}
	}

	/**
	 * Main function
	 * @param $data array Uni or bidimensionnal array
	 * @param $options array Array of options
	 * @param $putInCache string path of directory where cache will be recorded
	 * @param $id string index of svg tag
	 * @return string SVG 
	 *
	 * @author Cyril MAGUIRE
	 */
	public function draw($data,$options=array(),$putInCache=false,$id=false,$minify=true) {

		$nameOfFile = ($id ? $id : md5(date('Ymdhis')) );
		# Cache
		$nameOfFiles = glob($putInCache.'*.svg');
		if ($putInCache != false && isset($nameOfFiles[0])) {
			return file_get_contents($nameOfFiles[0]);
		}
		$return = '';

		$options = array_merge($this->options,$options);

		extract($options);

		if ($title) {
			$options['titleHeight'] = $titleHeight = 40;
		}
		if ($opacity < 0 || $opacity > 1) {
			$options['opacity'] = 0.5;
		}
		if (!is_string($diskLegendsLineColor)) {
			$diskLegendsLineColor = 'darkgrey';
		}

		$HEIGHT = $height+$titleHeight+$paddingTop;

		$heightLegends = 0;
		if (isset($legends) && !empty($legends)) {
			$heightLegends = count($legends)*30+2*$paddingTop;
		}

		$pie = '';

		if ($type != 'pie' && $type != 'ring') {
			# looking for min and max
			extract($this->__minMax($data,$type));
			$options['type'] = $type;
			
			extract($this->__xAxisConfig($type,$width,$max,$Xmax,$Xmin,$lenght,$options));

			$options['steps'] = $steps;

			$unitY = ($height/abs(($max+$steps)-$min));
			$gridV = $gridH = '';
			$x = $y = '';

			$headerDimensions = $this->__headerDimensions($widthViewBox,$HEIGHT,$heightLegends,$titleHeight,$paddingTop,$paddingLegendX,$lenght,$stepX);

			# Size of canevas will be bigger than grid size to display legends
			$return = $this->__header($headerDimensions,$responsive,$id);

			if ($type == 'stock' || (is_array($type) && in_array('stock',$type)) ) { 
				$plotLimit = $this->__stockDef();
			}
			if ($type == 'h-stock' || (is_array($type) && in_array('h-stock',$type)) ) { 
				$plotLimit = $this->__hstockDef();
			}
			# we draw the grid
			$return .= $this->__svgGrid($gradient,$width,$height,($paddingTop+$titleHeight));

			if ($title) {
				$return .= $this->__titleDef($title,$width,$titleHeight);
			}
			# Legends x axis and vertical grid
			extract($this->__XAxisDef($type,$Xmin,$Xmax,$XM,$stepX,$unitX,$HEIGHT,$paddingTop,$titleHeight,$labels,$lenght));

			# Legendes y axis and horizontal grid
			extract($this->__YAxisDef($type,$width,$min,$max,$steps,$HEIGHT,$titleHeight,$paddingTop,$paddingLegendX,$unitY,$labels));
			//Grid
			$return .= "\t".'<g class="graph-grid">'."\n";
			$return .= $gridH."\n"; 
			$return .= $gridV; 
			$return .= "\t".'</g>'."\n";

			$return .= $x;
			$return .= $y;
			if (!$multi) {
				if (is_array($type) && count($type) == 1) {
					$type = $type[0];
					$options['type'] = $type;
				}
				$options['stroke'] = is_array($stroke) ? $stroke[0] : $stroke;
				switch ($type) {
					case 'line':
						$return .= $this->__drawLine($data,$height,$HEIGHT,$stepX,$unitY,$lenght,$min,$max,$options);
						break;
					case 'bar':
						$return .= $this->__drawBar($data,$height,$HEIGHT,$stepX,$unitY,$lenght,$min,$max,$options);
						break;
					default:
						$return .= $this->__drawLine($data,$height,$HEIGHT,$stepX,$unitY,$lenght,$min,$max,$options);
						break;
				}
			} else {
				$i = 1;
				$oldId = $id;
				foreach ($data as $line => $datas) {
					$id = $oldId.'-'.$line;
					if (!isset($type[$line]) && !is_string($type) && is_numeric($line)) {
						$type[$line] = 'line';
					}
					if (!isset($type[$line]) && !is_string($type) && !is_numeric($line)) {
						$type[$line] = 'stock';
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
					$options['stroke'] = $STROKE = $stroke[$line];
					$options['fill'] = $stroke[$line];
					switch ($type[$line]) {
						case 'line':
							$return .= $this->__drawLine($datas,$height,$HEIGHT,$stepX,$unitY,$lenght,$min,$max,$options);
							break;
						case 'bar':
							$return .= $this->__drawBar($datas,$height,$HEIGHT,$stepX,$unitY,$lenght,$min,$max,$options);
							break;
						case 'stock':
							$id = rand();
							$return .= str_replace(array('id="plotLimit"','stroke=""'), array('id="plotLimit'.$id.'"','stroke="'.$stroke[$line].'"'), $plotLimit);
							$return .= $this->__drawStock($data,$height,$HEIGHT,$stepX,$unitY,$lenght,$min,$max,$options,$i,$labels,$id);
							$i++;
							break;
						case 'h-stock':
							$id = rand();
							$return .= str_replace(array('id="plotLimit"','stroke=""'), array('id="plotLimit'.$id.'"','stroke="'.$stroke[$line].'"'), $plotLimit);
							$return .= $this->__drawHstock($data,$HEIGHT,$stepX,$unitX,$unitY,$lenght,$Xmin,$Xmax,$options,$i,$labels,$id);
							$i++;
							break;
						case 'ring':
							$options['subtype'] = 'ring';
						case 'pie':
							$options['multi'] = $multi;
							if (is_array($stroke)) {
								$options['stroke'] = $stroke[$line];
								$options['fill'] = $stroke;
							}
							if (is_array($legends)) {
								$options['legends'] = $legends[$line];
							}
							$pie .= $this->__drawDisk($datas,$options,$id);
							$pie .= "\n".'</svg>'."\n";
							break;
						default:
							$return .= $this->__drawLine($datas,$height,$HEIGHT,$stepX,$unitY,$lenght,$min,$max,$options);
							break;
					}
				}
			}
			# legends
			$return .= $this->__legendsDef($legends,$type,$stroke,$HEIGHT,$paddingTop);
			$return .= "\n".'</svg>'."\n";
			$return .= $pie;
		} else {
			$options['tooltipLegend'] = array();
			if ($tooltipLegend && !is_array($tooltipLegend)) {
				foreach ($data as $key => $value) {
					$options['tooltipLegend'][] = $tooltipLegend;
				}
			}
			if ($tooltipLegend && is_array($tooltipLegend)) {
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
						$options['stroke'] = $stroke[$line];
						$options['fill'] = $stroke;
					}
					if (is_array($legends)) {
						$options['legends'] = $legends[$line];
					}
					$return .= $this->__drawDisk($datas,$options,$id);
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
				$return .= $this->__drawDisk($data,$options,$id);
				$return .= "\n".'</svg>'."\n";
			}

		}

		if ($minify) {
			$return = preg_replace("/(\r\n|\n|\r)/s", " ", $return);
			$return = str_replace(array("\t","\r\n","\n","\r",CHR(10),CHR(13)), '', trim($return));
		}

		$this->colors = array();
		if ($putInCache) {
			$this->putInCache(trim($return),$nameOfFile,$putInCache);
		}
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
	protected function __drawLine($data,$height,$HEIGHT,$stepX,$unitY,$lenght,$min,$max,$options) {
		$return = '';

		extract($options);

		$this->colors[] = $options['stroke'];
	
		//Ligne
		$i = 0;
		$c = '';
		$t = '';
		$path = "\t\t".'<path d="';
		foreach ($data as $label => $value) {

			if ($min <=0) {
				$V = ($value-$min);
			} else {
				$V = $value;
			}

			$coordonneesCircle1 = 'cx="'.($i * $stepX + 50).'" cy="'.($HEIGHT - $unitY*$V).'"';
			//$min == $value
			$coordonneesCircle2 = 'cx="'.($i * $stepX + 50).'" cy="'.($HEIGHT - $unitY*$V - $value*$unitY).'"';
			
			$coordonnees1 = ($i * $stepX + 50).' '.($HEIGHT - $unitY*$V);
			//$min == $value
			$coordonnees2 = ($i * $stepX + 50).' '.($HEIGHT - $unitY*$V - $value*$unitY);

			# Tooltips
			if($tooltips == true) {
				$c .= "\n\t\t".'<g class="graph-active">';
			}
			# Line
			$c1 = $this->__c($coordonneesCircle1,$stroke);
			$c2 = $this->__c($coordonneesCircle2,$stroke);
			if ($value != $max) {
				if ($value == $min) {
					if ($i == 0) {
						if ($min<=0) {
							$path .= 'M '.$coordonnees1.' L';
							//Tooltips and circles
							$c .= $c1;
						} else {
							$path .= 'M '.$coordonnees2.' L';
							//Tooltips and circles
							$c .= $c2;
						}
					} else {
						$path .= "\n\t\t\t\t".$coordonnees1;
						//Tooltips and circles
						$c .= $c1;
					}
				} else {
					if ($i == 0) {
						$path .= 'M '.$coordonnees1.' L';
						//Tooltips and circles
						$c .= $c1;
					} else {
						$path .= "\n\t\t\t\t".$coordonnees1;
						//Tooltips and circles
						$c .= $c1;
					}
				}
			} else {
				//Line
				if ($i == 0) {
					$path .= 'M '.$coordonnees1.' L';
					//Tooltips and circles
					$c .= "\n\t\t\t".'<circle '.$coordonneesCircle1.' r="3" stroke="'.$stroke.'" class="graph-point-active"/>';
				} else {
					$path .= "\n\t\t\t\t".$coordonnees1;
					//Tooltips and circles
					$c .= "\n\t\t\t".'<circle '.$coordonneesCircle1.' r="3" stroke="'.$stroke.'" class="graph-point-active"/>';
				}
				
			}
			$i++;
			//End tooltips
			if($tooltips == true) {
				$c .= "\n\t\t\t".'<title class="graph-tooltip">'.(is_array($tooltipLegend) ? $tooltipLegend[$i] : $tooltipLegend).$value.'</title>'."\n\t\t".'</g>';
			}
		}
		if ($opacity > 0.8 && $filled === true) {
			$tmp = $stroke;
			$stroke = '#a1a1a1';
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
			if ($opacity > 0.8) {
				$stroke = $tmp;
			}
			$return .= $path.'" class="graph-fill" fill="'.$stroke.'" fill-opacity="'.$opacity.'"/>'."\n";
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
	protected function __drawBar($data,$height,$HEIGHT,$stepX,$unitY,$lenght,$min,$max,$options) {
		$return = '';

		extract($options);
		
		$this->colors[] = $options['stroke'];

		//Bar
		$bar = '';
		$i = 0;
		$c = '';
		$t = '';
		foreach ($data as $label => $value) {

			if ($min <=0) {
				$V = ($value-$min);
			} else {
				$V = $value;
			}

			//Tooltips and circles
			if($tooltips == true) {
				$c .= "\n\t\t".'<g class="graph-active">';
			}

			$stepY = $value*$unitY;

			//$min>=0
			$coordonnees1 = 'x="'.($i * $stepX + 50).'" y="'.($HEIGHT - $unitY*$V).'"';
			//On recule d'un demi pas pour que la valeur de x soit au milieu de la barre de diagramme
			$coordonnees2 = 'x="'.($i * $stepX + 50 - $stepX/2).'" y="'.($HEIGHT - $stepY).'"';
			//$min<0
			$coordonnees3 = 'x="'.($i * $stepX + 50 - $stepX/2).'" y="'.($HEIGHT - $unitY*$V).'"';
			//$min<0 et $value<0
			$coordonnees4 = 'x="'.($i * $stepX + 50 - $stepX/2).'" y="'.($HEIGHT - $unitY*$V + $stepY).'"';
			$coordonnees5 = 'x="'.($i * $stepX + 50).'" y="'.($HEIGHT - $unitY*$V + $stepY).'"';
			//$min>=0 et $value == $max
			$coordonnees6 = 'x="'.($i * $stepX + 50).'" y="'.($paddingTop + $titleHeight).'"';
			//$value == 0
			$coordonnees7 = 'x="50" y="'.($HEIGHT + $unitY*$min).'"';
			if ($value == 0) {
				$stepY = 1;
			}
			//Diagramme
			//On est sur la première valeur, on divise la largeur de la barre en deux
			if ($i == 0) {
				if ($value == $max) {
					$bar .= "\n\t".'<rect '.$coordonnees6.' width="'.($stepX/2).'" height="'.$height.'" class="graph-bar" stroke="'.$stroke.'" fill="#fff" fill-opacity="0"/>';
					
					$c .= "\n\t\t\t".'<circle c'.str_replace('y="', 'cy="', $coordonnees6).' r="3" stroke="'.$stroke.'" class="graph-point-active"/>';
					
				} else {
					if ($min>=0) {
						$bar .= "\n\t".'<rect '.$coordonnees1.' width="'.($stepX/2).'" height="'.$stepY.'" class="graph-bar" stroke="'.$stroke.'" fill="#fff" fill-opacity="0"/>';
						
						$c .= "\n\t\t\t".'<circle c'.str_replace('y="', 'cy="', $coordonnees1).' r="3" stroke="'.$stroke.'" class="graph-point-active"/>';
						
					} else {
						if ($value == $min) {
							$bar .= "\n\t".'<rect '.$coordonnees5.' width="'.($stepX/2).'" height="'.-$stepY.'" class="graph-bar" stroke="'.$stroke.'" fill="#fff" fill-opacity="0"/>';
						} else {
							if ($value == 0) {
								$bar .= "\n\t".'<rect '.$coordonnees7.' width="'.($stepX/2).'" height="1" class="graph-bar" stroke="'.$stroke.'" fill="#fff" fill-opacity="0"/>';
							} else {
								$bar .= "\n\t".'<rect '.$coordonnees1.' width="'.($stepX/2).'" height="'.$stepY.'" class="graph-bar" stroke="'.$stroke.'" fill="#fff" fill-opacity="0"/>';
							}
						}
						
						$c .= "\n\t\t\t".'<circle c'.str_replace('y="', 'cy="', $coordonnees1).' r="3" stroke="'.$stroke.'" class="graph-point-active"/>';
						
					}
					
				}
			} else {
				if ($value == $max) {
					if ($min>=0) {
						//Si on n'est pas sur la dernière valeur
						if ($i != $lenght-1) {
							$bar .= "\n\t".'<rect '.$coordonnees2.' width="'.$stepX.'" height="'.$stepY.'" class="graph-bar" stroke="'.$stroke.'" fill="#fff" fill-opacity="0"/>';
							
						} else {
							$bar .= "\n\t".'<rect '.$coordonnees2.' width="'.($stepX/2).'" height="'.$height.'" class="graph-bar" stroke="'.$stroke.'" fill="#fff" fill-opacity="0"/>';
						}
						$c .= "\n\t\t\t".'<circle c'.str_replace('y="', 'cy="', $coordonnees1).' r="3" stroke="'.$stroke.'" class="graph-point-active"/>';
					} else {
						if ($value >= 0) {
							//Si on n'est pas sur la dernière valeur
							if ($i != $lenght-1) {
								$bar .= "\n\t".'<rect '.$coordonnees3.' width="'.$stepX.'" height="'.$stepY.'" class="graph-bar" stroke="'.$stroke.'" fill="#fff" fill-opacity="0"/>';
							} else {
								$bar .= "\n\t".'<rect '.$coordonnees3.' width="'.($stepX/2).'" height="'.$stepY.'" class="graph-bar" stroke="'.$stroke.'" fill="#fff" fill-opacity="0"/>';
							}
						} else {
							//Si on n'est pas sur la dernière valeur
							if ($i != $lenght-1) {
								$bar .= "\n\t".'<rect '.$coordonnees4.' width="'.$stepX.'" height="'.-$stepY.'" class="graph-bar" stroke="'.$stroke.'" fill="#fff" fill-opacity="0"/>';
							} else {
								$bar .= "\n\t".'<rect '.$coordonnees4.' width="'.($stepX/2).'" height="'.-$stepY.'" class="graph-bar" stroke="'.$stroke.'" fill="#fff" fill-opacity="0"/>';
							}
						}
						
						$c .= "\n\t\t\t".'<circle c'.str_replace('y="', 'cy="', $coordonnees1).' r="3" stroke="'.$stroke.'" class="graph-point-active"/>';
						
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
								$bar .= "\n\t".'<rect '.$coordonnees3.' width="'.$stepX.'" height="'.($stepY).'" class="graph-bar" stroke="'.$stroke.'" fill="#fff" fill-opacity="0"/>';
							} else {
								$bar .= "\n\t".'<rect '.$coordonnees3.' width="'.($stepX/2).'" height="'.$stepY.'" class="graph-bar" stroke="'.$stroke.'" fill="#fff" fill-opacity="0"/>';
							}
						} else {
							//Si on n'est pas sur la dernière valeur
							if ($i != $lenght-1) {
								$bar .= "\n\t".'<rect '.$coordonnees4.' width="'.$stepX.'" height="'.-$stepY.'" class="graph-bar" stroke="'.$stroke.'" fill="#fff" fill-opacity="0"/>';
							} else {
								$bar .= "\n\t".'<rect '.$coordonnees4.' width="'.($stepX/2).'" height="'.-$stepY.'" class="graph-bar" stroke="'.$stroke.'" fill="#fff" fill-opacity="0"/>';
							}
						}
						
						$c .= "\n\t\t\t".'<circle c'.str_replace('y="', 'cy="', $coordonnees1).' r="3" stroke="'.$stroke.'" class="graph-point-active"/>';
						
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
			if ($opacity == 1) {
				$opacity = '1" stroke="#424242';
			}
			$barFilled = str_replace(' class="graph-bar" stroke="'.$stroke.'" fill="#fff" fill-opacity="0"/>', ' class="graph-bar" fill="'.$stroke.'" fill-opacity="'.$opacity.'"/>',$bar);
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
	 * To draw pie diagrams
	 * @param $data array Unidimensionnal array
	 * @param $options array Options
	 * @return string Path of lines (with options)
	 *
	 * @author Cyril MAGUIRE
	 */
	protected function __drawDisk($data,$options=array(),$id=false) {

		$options = array_merge($this->options,$options);

		extract($options);

		if (is_string($legends)) {
			$mainLegend = $legends;
		} else {
			$mainLegend = null;
		}
		if (is_string($stroke)) {
			$mainStroke = $stroke;
		} else {
			$mainStroke = $this->__genColor();
		}
		
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
					'tooltipLegend' => (is_array($tooltipLegend) && isset($tooltipLegend[$i])) ? $tooltipLegend[$i] : (isset($tooltipLegend) && is_string($tooltipLegend) ? $tooltipLegend : ''),
					'stroke' => (is_array($stroke) && isset($stroke[$i]))? $stroke[$i] : $this->__genColor(),
				);
			} else {
				$deg[] = array(
					'pourcent' => round(((($value * 100)/$total)/100),2),
					'val' => $value,
					'label' => $label,
					'tooltipLegend' => (is_array($tooltipLegend) && isset($tooltipLegend[$i])) ? $tooltipLegend[$i] : (isset($tooltipLegend) && is_string($tooltipLegend) ? $tooltipLegend : ''),
					'stroke' => (is_array($stroke) && isset($stroke[$i]) ) ? $stroke[$i] : $this->__genColor(),
				);
			}
			$i++;
		}
		if (isset($legends)) {
			if (!is_array($legends) && !empty($legends) && !is_bool($legends)) {
				$Legends = array();
				for ($l=0;$l<$lenght;$l++) {
					$Legends[$l] = array( 
						'label' => $deg[$l]['label'].' : '.$deg[$l]['val'],
						'stroke' => $deg[$l]['stroke']
					);
				}
				$legends = $Legends;
				unset($Legends);
			} elseif (empty($legends)) {
				$notDisplayLegends = true;
			} elseif (is_bool($legends)) {
				$legends = array();
			}
			foreach ($deg as $k => $v) {
				if (!isset($legends[$k]) || !is_array($legends[$k])) {
					$legends[$k] = array(
						'label' => $v['label'].' : '.$v['val'],
						'stroke' => $v['stroke']
					);
				}
			}	
		}
		$deg = array_reverse($deg);

		$heightLegends = 0;
		if (isset($legends) && !empty($legends)) {
			$heightLegends = count($legends)*30+2*$paddingTop+80;
		} else {
			$heightLegends = 2*$paddingTop+100;
		}

		$this->colors[] = $options['stroke'];

		$originX = (2*$radius+400)/2;
		$originY = 10+$titleHeight+2*$paddingTop;


		//Size of canevas will be bigger than grid size to display legends
		$return = "\n".'<svg width="100%" height="100%" viewBox="0 0 '.(2*$radius+400).' '.(2*$radius+100+$titleHeight+$paddingTop+$heightLegends).'" preserveAspectRatio="xMidYMid meet" xmlns="http://www.w3.org/2000/svg" version="1.1"'.($id ? ' id="'.$id.'"':'').'>'."\n";
		$return .= '<defs>';
	    $return .= "\n\t\t".'<style type="text/css">//<![CDATA[
	      '.$this->css.'
	    ]]></style>'."\n";
		// $return .= "\n\t\t".'<marker id="Triangle"';
		// $return .= "\n\t\t\t".'viewBox="0 0 10 10" refX="0" refY="5"';
		// $return .= "\n\t\t\t".'markerUnits="strokeWidth"';
		// $return .= "\n\t\t\t".'markerWidth="10" markerHeight="3"';
		// $return .= "\n\t\t\t".'fill="#a1a1a1" fill-opacity="0.7"';
		// $return .= "\n\t\t\t".'orient="auto">';
		// $return .= "\n\t\t\t".'<path d="M 0 0 L 10 5 L 0 10 z" />';
		// $return .= "\n\t\t".'</marker>';
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
			$return .= '</defs>'."\n";
		}
		
		if (isset($title)) {
			$return .= "\t".'<text x="'.($originX).'" y="'.$titleHeight.'" text-anchor="middle" class="graph-title">'.$title.'</text>'."\n";
		}

		$ox = $prevOriginX = $originX;
		$oy = $prevOriginY = $originY;
		$total = 1;

		$i = 0;
		while ($i <= $lenght-1) { 

			if ($deg[0]['val'] != 0) {
				$t = (2-$deg[0]['pourcent'])/2;
				$cosOrigine = cos((-90 + 360 * $t) * M_PI / 180)*$radius;
				$sinOrigine = sin((-90 + 360 * $t) * M_PI / 180)*$radius;
				$cosLegOrigine = cos((-90 + 360 * $t) * M_PI / 180)*(2*$radius);
				$sinLegOrigine = sin((-90 + 360 * $t) * M_PI / 180)*(2*$radius);
			} else {
				$cosOrigine = 0;
				$sinOrigine = 0;
				$cosLegOrigine = 0;
				$sinLegOrigine = 0;
			}

			if ($deg[$i]['val'] != 0) {
				//Tooltips
				if($tooltips == true) {
					$return .= "\n\t\t".'<g class="graph-active">';
				}
				$color = $deg[0]['stroke'];
				$return .= "\n\t\t\t".'<circle cx="'.$originX.'" cy="'.($originY+2*$radius).'" r="'.$radius.'" fill="'.$color.'" class="graph-pie"/>'."\n\t\t\t";

				if ($diskLegends == true && $deg[0]['val'] != 0 ){

					if ($deg[0]['pourcent'] >= 0 && $deg[0]['pourcent'] <= 0.5 || $deg[0]['pourcent'] == 1) {
						$gapx = $gapy = 0;				
						$pathGap = 2;
					}
					if($deg[0]['pourcent'] > 0.5 && $deg[0]['pourcent'] < 1) {
						$gapx = $gapy = 1;
						$pathGap = 1;
					}

					$LABEL = ($diskLegendsType == 'label' ? $deg[$i]['label'] : ($diskLegendsType == 'pourcent' ? ($deg[$i]['pourcent']*100).'%' : $deg[$i]['val']));

					if ($gapx == -1) {
						$gapx = strlen($LABEL)*$gapx*12;
						$gapy = 5;
					}

					$return .= "\n\t\t\t".'<path d=" M '.($originX+$cosOrigine).' '.($originY+2*$radius+$sinOrigine).' L '.($originX+$cosLegOrigine).' '.($originY + 2*$radius + $sinLegOrigine).' L '.($originX+$cosLegOrigine-30).' '.($originY + 2*$radius + $sinLegOrigine).'" class="graph-line" stroke="'.$diskLegendsLineColor.'" stroke-opacity="0.5" stroke-dasharray="2,2,2" fill="none"/>';

					$return .= "\n\t\t\t".'<text x="'.($originX+$cosLegOrigine+$gapx-30*$pathGap).'" y="'.($originY+2*$radius+$sinLegOrigine+$gapy-5).'" class="graph-legend" stroke="darkgrey" stroke-opacity="0.5">'.$LABEL.'</text>'."\n\t\t\t";
				}
				
				//End tooltips
				if($tooltips == true) {
					$return .= '<title class="graph-tooltip">'.$deg[$i]['tooltipLegend']. $deg[$i]['label'].' : '.$deg[$i]['val'].'</title>';
					$return .= "\n\t\t".'</g>';
				}
				//$i = $deg[$i]['label'];
				break;
			}
			$i++;
		}
		// $tmp = array(); 
		// if (is_array($legends)) {
		// 	foreach($legends as &$ma) {
		// 		$tmp[] = &$ma['label'];
		// 	}
		// 	array_multisort($tmp, $legends); 
		// }
		
		foreach ($deg as $key => $value) {

				$total -= $value['pourcent'];
				$total2 = $total;

				$cos = cos((-90 + 360 * $total) * M_PI / 180)*$radius;
				$sin = sin((-90 + 360 * $total) * M_PI / 180)*$radius;

				$cosLeg = cos((-90 + 360 * $total) * M_PI / 180)*(2*$radius);
				$sinLeg = sin((-90 + 360 * $total) * M_PI / 180)*(2*$radius);

				if (isset($deg[$key+1])) {
					$total2 -= $deg[$key+1]['pourcent'];
					$t = (($total - $total2)/2) + $total2;
					$cos2 = cos((-90 + 360 * $t) * M_PI / 180)*$radius;
					$sin2 = sin((-90 + 360 * $t) * M_PI / 180)*$radius;
					$cosLeg2 = cos((-90 + 360 * $t) * M_PI / 180)*(2*$radius);
					$sinLeg2 = sin((-90 + 360 * $t) * M_PI / 180)*(2*$radius);
				} else {
					$cos2 = 0;
					$sin2 = 0;
					$cosLeg2 = 0;
					$sinLeg2 = 0;
				}

				//Tooltips
				if($tooltips == true && $key < ($lenght-1)) {
					$return .= "\n\t\t".'<g class="graph-active">';
				}
				
				if ($total >= 0 && $total <= 0.5 || $total == 1) {
					$arc = 0;
					$gapx = $gapy = 0;
					$signe = 1;
					$pathGap = 1;
				}
				if($total > 0.5 && $total < 1) {
					$arc = 1;
					$signe = -1;
					$pathGap = 2.5;
				}
				$index = ($key == $lenght-1 ? 0 : $key+1);

				if ($key != $lenght-1 && $deg[$index]['val'] != 0) {
					$return .= "\n\t\t\t".'<path d="M '.$originX.' '.($originY + $radius).'  A '.$radius.' '.$radius.'  0 '.$arc.' 1 '.($originX + $cos).' '.($originY + 2*$radius + $sin).' L '.$originX.' '.($originY+2*$radius).' z" fill="'.$deg[$index]['stroke'].'" class="graph-pie"/>'."\n\t\t\t";
				}

				if ($key < ($lenght-1) && $deg[$key+1]['val'] != 0 && $diskLegends == true ) {

					$LABEL = ($diskLegendsType == 'label' ?  $deg[$key+1]['label'] : ($diskLegendsType == 'pourcent' ? ($deg[$key+1]['pourcent']*100).'%' : $deg[$key+1]['val']));

					if ($arc == 1) {
						$gapx = strlen($LABEL)*$gapx*12;
						$gapy = 5;
					}

					$return .= "\n\t\t\t".'<path d=" M '.($originX+$cos2).' '.($originY+2*$radius+$sin2).' L '.($originX + $cosLeg2).' '.($originY + 2*$radius + $sinLeg2).' L '.($originX+$cosLeg2+$signe*30).' '.($originY + 2*$radius + $sinLeg2).'" fill="none" class="graph-line" stroke="'.$diskLegendsLineColor.'" stroke-opacity="0.5"  stroke-dasharray="2,2,2"/>';

					$return .= "\n\t\t\t".'<text x="'.($originX + $cosLeg2 + $gapx + $signe*30*$pathGap).'" y="'.($originY + 2*$radius + $sinLeg2 + $gapy).'" class="graph-legend" stroke="darkgrey" stroke-opacity="0.5">  '.$LABEL.'</text>'."\n\t\t\t";
				}
				//End tooltips
				if($tooltips == true && $key < ($lenght-1)) {
					$return .= '<title class="graph-tooltip">'.$deg[$key+1]['tooltipLegend'].$deg[$key+1]['label'].' : '.$deg[$key+1]['val'].'</title>'."\n\t\t".'</g>';
				}
		}

		if ($mainLegend) {
			$return .= '<rect x="50" y="'.(4*$radius+$titleHeight+$paddingTop+(2*$paddingTop)+30).'" width="10" height="10" fill="'.$mainStroke.'" class="graph-legend-stroke"/>
			<text x="70" y="'.(4*$radius+$titleHeight+$paddingTop+(2*$paddingTop)+40).'" class="graph-legend">'.$mainLegend.'</text>';
			$paddingLegendY += 70;
		}
		if (isset($legends) && !empty($legends) && !isset($notDisplayLegends)) {
			$leg = "\t".'<g class="graph-legends">';
			foreach ($legends as $key => $value) {
				$colorToFillWith = ($key == 0 ? $value['stroke'] : $deg[$lenght-$key-1]['stroke']);
				$leg .= "\n\t\t".'<rect x="70" y="'.(4*$radius+$titleHeight+$paddingTop+$paddingLegendY+$key*(2*$paddingTop)).'" width="10" height="10" fill="'.$colorToFillWith.'" class="graph-legend-stroke"/>';
				$leg .= "\n\t\t".'<text x="90" y="'.(4*$radius+$titleHeight+$paddingTop+$paddingLegendY+10+$key*(2*$paddingTop)).'" text-anchor="start" class="graph-legend">'.$value['label'].'</text>';
			}
			$leg .= "\n\t".'</g>';

			$return .= $leg;
		}
		if ($type == 'ring' || isset($subtype)) {
			$return .= '<circle cx="'.$originX.'" cy="'.($originY+2*$radius).'" r="'.($radius/2).'" fill="'.$background.'" class="graph-pie"/>';
		}

		return $return;
	}
	
	/**
	 * To draw vertical stock chart
	 * @param $data array Array with structure equal to array('index'=> array('open'=>val,'close'=>val,'min'=>val,'max'=>val))
	 * @param $height integer Height of grid
	 * @param $HEIGHT integer Height of grid + title + padding top
	 * @param $stepX integer Distance between two graduations on x-axis
	 * @param $unitY integer Unit of y-axis
	 * @param $lenght integer Number of graduations on x-axis
	 * @param $min integer Minimum value of data
	 * @param $max integer Maximum value of data
	 * @param $options array Options
	 * @param $i integer index of current data
	 * @param $labels array labels of x-axis
	 * @param $id integer index of plotLimit
	 * @return string Path of lines (with options)
	 *
	 * @author Cyril MAGUIRE
	 */
	protected function __drawStock($data,$height,$HEIGHT,$stepX,$unitY,$lenght,$min,$max,$options,$i,$labels,$id) {
		$error = null;
		if (!isset($data[$labels[$i]]['open'])) { 
			$error[] = 'open';
		}
		if (!isset($data[$labels[$i]]['close'])) { 
			$error[] = 'close';
		}
		if (!isset($data[$labels[$i]]['max'])) { 
			$error[] = 'max';
		}
		if (!isset($data[$labels[$i]]['min'])) { 
			$error[] = 'min';
		}
		if ($error) {
			$return = "\t\t".'<path id="chemin" d="M '.($i * $stepX + 50).' '.($HEIGHT-$height+10).' V '.$height.'" class="graph-line" stroke="transparent" fill="#fff" fill-opacity="0"/>'."\n";
			$return .= "\t\t".'<text><textPath xlink:href="#chemin">Error : "';
			foreach ($error as $key => $value) {
				$return .= $value.(count($error)>1? ' ' : '');
			}
			$return .= '" missing</textPath></text>'."\n";
			return $return;
		}
		$options = array_merge($this->options,$options);

		extract($options);

		$return = '';
		if($data[$labels[$i]]['close'] < $data[$labels[$i]]['open']) {
			$return .= "\n\t".'<rect x="'.($i * $stepX + 50 - $stepX/4).'" y="'.($HEIGHT - $unitY*$data[$labels[$i]]['open']).'" width="'.($stepX/2).'" height="'.($unitY*$data[$labels[$i]]['open'] - ($unitY*$data[$labels[$i]]['close'])).'" class="graph-bar" fill="'.$stroke.'" fill-opacity="1"/>';
		}
		if($data[$labels[$i]]['close'] == $data[$labels[$i]]['open']) {
			$return .= "\n\t".'<path d="M'.($i * $stepX + 50 + 5).' '.($HEIGHT - $unitY*$data[$labels[$i]]['open']).' l -5 -5, -5 5, 5 5 z" class="graph-line" stroke="'.$stroke.'" fill="'.$stroke.'" fill-opacity="1"/>';
		}
		//Limit Up
		$return .= "\n\t".'<path d="M'.($i * $stepX + 50).' '.($HEIGHT - $unitY*$data[$labels[$i]]['close']).'  L'.($i * $stepX + 50).' '.($HEIGHT-$unitY*$data[$labels[$i]]['max']).' " class="graph-line" stroke="'.$stroke.'" fill="#fff" fill-opacity="0"/>';
		$return .= '<use xlink:href="#plotLimit'.$id.'" transform="translate('.($i * $stepX + 50 - 5).','.($HEIGHT-$unitY*$data[$labels[$i]]['max']).')"/>';
		//Limit Down
		$return .= "\n\t".'<path d="M'.($i * $stepX + 50).' '.($HEIGHT - $unitY*$data[$labels[$i]]['open']).'  L'.($i * $stepX + 50).' '.($HEIGHT-$unitY*$data[$labels[$i]]['min']).' " class="graph-line" stroke="'.$stroke.'" fill="#fff" fill-opacity="0"/>';
		$return .= '<use xlink:href="#plotLimit'.$id.'" transform="translate('.($i * $stepX + 50 - 5).','.($HEIGHT-$unitY*$data[$labels[$i]]['min']).')"/>';
		if($tooltips == true) {
			//Open
			$return .= $this->__circle($i,$stepX,($HEIGHT - $unitY*$data[$labels[$i]]['open']),$data[$labels[$i]]['open'],$stroke);
			//Close
			$return .= $this->__circle($i,$stepX,($HEIGHT - $unitY*$data[$labels[$i]]['close']),$data[$labels[$i]]['close'],$stroke);
			//Max
			$return .= $this->__circle($i,$stepX,($HEIGHT - $unitY*$data[$labels[$i]]['max']),$data[$labels[$i]]['max'],$stroke);
			//Min
			$return .= $this->__circle($i,$stepX,($HEIGHT - $unitY*$data[$labels[$i]]['min']),$data[$labels[$i]]['min'],$stroke);
		}
		return $return;
	}
	
	/**
	 * To draw horizontal stock chart
	 * @param $data array Array with structure equal to array('index'=> array('open'=>val,'close'=>val,'min'=>val,'max'=>val))
	 * @param $HEIGHT integer Height of grid + title + padding top
	 * @param $stepX integer Distance between two graduations on x-axis
	 * @param $unitX integer Unit of x-axis
	 * @param $unitY integer Unit of y-axis
	 * @param $lenght integer Number of graduations on y-axis
	 * @param $Xmin integer Minimum value of data
	 * @param $Xmax integer Maximum value of data
	 * @param $options array Options
	 * @param $i integer index of current data
	 * @param $labels array labels of y-axis
	 * @param $id integer index of plotLimit
	 * @return string Path of lines (with options)
	 *
	 * @author Cyril MAGUIRE
	 */
	protected function __drawHstock($data,$HEIGHT,$stepX,$unitX,$unitY,$lenght,$Xmin,$Xmax,$options,$i,$labels,$id) {
		if($i>0) {$i--;}

		$stepY = $HEIGHT - ($unitY*($i+1));

		$error = null;
		if (!isset($data[$labels[$i]]['open'])) { 
			$error[] = 'open';
		}
		if (!isset($data[$labels[$i]]['close'])) { 
			$error[] = 'close';
		}
		if (!isset($data[$labels[$i]]['max'])) { 
			$error[] = 'max';
		}
		if (!isset($data[$labels[$i]]['min'])) { 
			$error[] = 'min';
		}
		if ($error) {
			$return = "\t\t".'<path id="chemin" d="M '.(2*$unitX + 50).' '.$stepY.' H '.(($Xmax-$Xmin)*$unitX).'" class="graph-line" stroke="transparent" fill="#fff" fill-opacity="0"/>'."\n";
			$return .= "\t\t".'<text><textPath xlink:href="#chemin">Error : "';
			foreach ($error as $key => $value) {
				$return .= $value.(count($error)>1? ' ' : '');
			}
			$return .= '" missing</textPath></text>'."\n";
			return $return;
		}
		$options = array_merge($this->options,$options);

		extract($options);

		$return = '';
		if($data[$labels[$i]]['close'] > $data[$labels[$i]]['open']) {
			$return .= "\n\t".'<rect x="'.($unitX*$data[$labels[$i]]['open']+50).'" y="'.($stepY-10).'" width="'.(($unitX*$data[$labels[$i]]['close']) - ($unitX*$data[$labels[$i]]['open'])).'" height="20" class="graph-bar" fill="'.$stroke.'" fill-opacity="1"/>';
		}
		if($data[$labels[$i]]['close'] == $data[$labels[$i]]['open']) {
			$return .= "\n\t".'<path d="M'.($unitX*$data[$labels[$i]]['open']+50+5).' '.($stepY).' l -5 -5, -5 5, 5 5 z" class="graph-line" stroke="'.$stroke.'" fill="'.$stroke.'" fill-opacity="1"/>';
		}
		// //Limit Up
		$return .= "\n\t".'<path d="M'.($unitX*$data[$labels[$i]]['max']+50).' '.($stepY).'  L'.($unitX*$data[$labels[$i]]['close']+50).' '.($stepY).' " class="graph-line" stroke="'.$stroke.'" fill="#fff" fill-opacity="0"/>';
		$return .= '<use xlink:href="#plotLimit'.$id.'" transform="translate('.($unitX*$data[$labels[$i]]['max']+50).','.($stepY-5).')"/>';
		// //Limit Down
		$return .= "\n\t".'<path d="M'.($unitX*$data[$labels[$i]]['min']+50).' '.($stepY).'  L'.($unitX*$data[$labels[$i]]['open']+50).' '.($stepY).' " class="graph-line" stroke="'.$stroke.'" fill="#fff" fill-opacity="0"/>';
		$return .= '<use xlink:href="#plotLimit'.$id.'" transform="translate('.($unitX*$data[$labels[$i]]['min']+50).','.($stepY-5).')"/>';
		if($tooltips == true) {
			//Open
			$return .= $this->__circle($unitX,$data[$labels[$i]]['open'],$stepY,$data[$labels[$i]]['open'],$stroke);
			//Close
			$return .= $this->__circle($unitX,$data[$labels[$i]]['close'],$stepY,$data[$labels[$i]]['close'],$stroke);
			//Max
			$return .= $this->__circle($unitX,$data[$labels[$i]]['max'],$stepY,$data[$labels[$i]]['max'],$stroke);
			//Min
			$return .= $this->__circle($unitX,$data[$labels[$i]]['min'],$stepY,$data[$labels[$i]]['min'],$stroke);
		}
		return $return;
	}

	/**
	 * To add circles on histogram
	 * @param $unitX integer index of current data
	 * @param $cx integer Distance between two graduations on x-axis
	 * @param $cy integer Distance between two graduations on y-axis
	 * @param $label string label of tooltip
	 * @param $stroke string color of circle
	 * @return string path of circle
	 *
	 * @author Cyril MAGUIRE
	 */
	protected function __circle($unitX,$cx,$cy,$label,$stroke) {
		$return = "\n\t\t".'<g class="graph-active">';
		$return .= "\n\t\t\t".'<circle cx="'.($unitX*$cx+50).'" cy="'.$cy.'" r="1" stroke="'.$stroke.'" opacity="0" class="graph-point-active"/>';
		$return .= "\n\t".'<title class="graph-tooltip">'.$label.'</title>'."\n\t\t".'</g>';
		return $return;
	}

	/**
	 * To research min and max values of data depends on type of graph
	 * @param $data array data
	 * @param $type string line, bar, pie, ring, stock or h-stock
	 * @return array of variables needed for draw method
	 *
	 * @author Cyril MAGUIRE
	 */
	protected function __minMax($data,$type) {
		$arrayOfMin = $arrayOfMax = $arrayOfLenght = $labels = $tmp['type'] = array();
		$valuesMax = $valuesMin = '';
		$Xmin = $Xmax = null;
		$multi = false;
		//For each diagrams with several lines/histograms
		foreach ($data as $line => $datas) {
			if ($type == 'stock' || (is_array($type) && in_array('stock',$type))|| $type == 'h-stock' || (is_array($type) && in_array('h-stock',$type)) ) {
				$arrayOfMin[] = isset($datas['min']) ? floor($datas['min']):0;
				$arrayOfMax[] = isset($datas['max']) ?  ceil($datas['max']) : 0;
				$arrayOfLenght[] = count($data);
				$labels = array_merge(array_keys($data),$labels);
				if (is_string($type)) {
					$tmp['type'][$line] = $type;
				}
				$multi = true;
			} else {
				if (is_array($datas)) {
					$valuesMax = array_map('ceil', $datas);
					$valuesMin = array_map('ceil', $datas);
					$arrayOfMin[] = min($valuesMin);
					$arrayOfMax[] = max($valuesMax);
					$arrayOfLenght[] = count($datas);
					$labels = array_merge(array_keys($datas),$labels);
					if (is_string($type)) {
						$tmp['type'][] = $type;
					}
					$multi = true;
				} else {
					$multi = false;
				}
			}
		}

		if ($multi == true) {
			if (!empty($tmp['type'])) {
				$type = $tmp['type'];
			}
			unset($tmp);

			$labels = array_unique($labels);

			if ($type == 'h-stock' || (is_array($type) && in_array('h-stock',$type)) ) {
				$min = 0;
				$max = count($labels);
				$Xmax = max($arrayOfMax);
				$Xmin = min($arrayOfMin);
				$lenght = $Xmax - $Xmin;
			} else {
				$min = min($arrayOfMin);
				$max = max($arrayOfMax);
				$lenght = max($arrayOfLenght);
			}
			if ($type == 'stock' || (is_array($type) && in_array('stock',$type)) ) {
				array_unshift($labels,'');
				$labels[] = '';
				$lenght += 2;
			}
		} else {
			$labels = array_keys($data);
			$lenght = count($data);
			$min = min($data);
			$max = max($data);
		}
		return array('min'=>$min, 'max'=>$max, 'lenght'=>$lenght, 'labels'=>$labels, 'multi'=>$multi, 'valuesMax'=>$valuesMax, 'valuesMin'=>$valuesMin, 'Xmin'=>$Xmin, 'Xmax'=>$Xmax, 'type' => $type);
	}

	/**
	 * Calcul of variables needed for x-axis configuration
	 * @param $type string type of graph
	 * @param $width integer width of grid
	 * @param $max integer max of values in data
	 * @param $Xmax integer max of values in x-axis
	 * @param $Xmin integer min of values in x-axis
	 * @param $lenght integer distance between min and max values
	 * @param $options array options
	 * @return array of variables needed for draw method
	 *
	 * @author Cyril MAGUIRE
	 */
	protected function __xAxisConfig($type,$width,$max,$Xmax,$Xmin,$lenght,$options=false) {
		$XM = $stepX = $unitX = null;
		if ($type == 'h-stock' || (is_array($type) && in_array('h-stock',$type)) ) {

			$l = strlen(abs($Xmax))-1;
			if ($l == 0) {
				$l = 1;
				$XM = ceil($Xmax); # Max value to display in x axis
				$stepX = 1;
				$M = $lenght+1; # temporary max
				$steps = 1;
				if($XM == 0) {$XM = 1;}
				$unitX = $width/$XM;
				$widthViewBox = $width+$XM+50;
			} else {
				$XM =  ceil($Xmax/($l*10))*($l*10);
				$stepX = $l*10;
				$M = $lenght+1;
				$steps = 1;
				if ($Xmin>0 || ($Xmin<0 && $Xmax<0)) {
					$Xmin = 0;
				}
				if($XM == 0) {$XM = 1;}
				$unitX = ($width/$XM);
				$widthViewBox = $width + ($XM/$stepX)*$unitX;
			}
		} else {
			
			$l = strlen(abs($max))-1;
			if ($l == 0) {
				$l = 1;
				$M =  ceil($max);
				$steps = 1;
			}else {
				$M =  ceil($max/($l*10))*($l*10);
				$steps = $l*10;
			}
			
			$max = $M;
			if (isset($options['steps']) && is_int($steps)) {
				$steps = $options['steps'];
			}

			$stepX = $width / ($lenght - 1);
			$widthViewBox = $lenght*$stepX+$stepX;
		}
		return array('XM'=>$XM,'stepX'=>$stepX,'steps'=>$steps,'unitX'=>$unitX,'widthViewBox'=>$widthViewBox,'Xmin'=>$Xmin);
	}

	/**
	 * To draw x-axis of the grid
	 * @param $type string type of graph
	 * @param $Xmin integer min of values in x-axis
	 * @param $Xmax integer max of values in x-axis
	 * @param $XM integer Max value to display in x axis
	 * @param $stepX integer Distance between two graduations on x-axis
	 * @param $unitX integer index of current data
	 * @param $HEIGHT integer height of the entire svg tag
	 * @param $paddingTop integer padding on top 
	 * @param $titleHeight integer height of title
	 * @param $labels array Array of labels
	 * @param $lenght integer distance between min and max values
	 * @return array of variables needed for draw method
	 *
	 * @author Cyril MAGUIRE
	 */
	protected function __XAxisDef($type,$Xmin,$Xmax,$XM,$stepX,$unitX,$HEIGHT,$paddingTop,$titleHeight,$labels,$lenght) {
		$gridV = $x = '';
		$x .= "\t".'<g class="graph-x">'."\n";
		if (is_array($type) && in_array('h-stock', $type) ) {
			for ($i=$Xmin; $i <= $XM; $i+=$stepX) {
				//1 graduation every $steps units
				$step = $unitX*$i;

				$x .= "\t\t".'<text x="'.(50+$step).'" y="'.($HEIGHT+2*$paddingTop).'" text-anchor="end" baseline-shift="-1ex" dominant-baseline="middle">'.$i.'</text>'."\n";
				//Vertical grid
				if ($i != $Xmax) {
					$gridV .= "\t\t".'<path d="M '.(50+$step).' '.($paddingTop+$titleHeight).' V '.($HEIGHT).'"/>'."\n" ;
				}
			}
		} else {
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
		}
		$x .= "\t".'</g>'."\n";
		return array('x'=>$x, 'gridV'=>$gridV);
	}

	/**
	 * To draw y-axis of the grid
	 * @param $type string type of graph
	 * @param $width integer width of grid
	 * @param $min integer min of values in data
	 * @param $max integer max of values in data
	 * @param $steps integer Distance between two graduations
	 * @param $HEIGHT integer height of the entire svg tag
	 * @param $titleHeight integer height of title
	 * @param $paddingTop integer padding on top 
	 * @param $paddingLegendX integer padding between legend and x axis
	 * @param $unitY integer unit of y graduations
	 * @param $labels array Array of labels
	 * @return array of variables needed for draw method
	 *
	 * @author Cyril MAGUIRE
	 */
	protected function __YAxisDef($type,$width,$min,$max,$steps,$HEIGHT,$titleHeight,$paddingTop,$paddingLegendX,$unitY,$labels) {
		$gridH = $y = '';
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
		
			if ($stepY >= ($titleHeight+$paddingTop+$paddingLegendX)) {
				if (is_array($type) && in_array('h-stock', $type) && isset($labels[$i-1])) {
					$y .= "\t\t".'<g class="graph-active"><text x="40" y="'.$stepY.'" text-anchor="end" baseline-shift="-1ex" dominant-baseline="middle" >'.($i > 0 ? (strlen($labels[$i-1]) > 3 ? substr($labels[$i-1],0,3).'.</text><title>'.$labels[$i-1].'</title>' : $labels[$i-1].'</text>') : '</text>')."</g>\n";
				} else {
					$y .= "\t\t".'<text x="40" y="'.$stepY.'" text-anchor="end" baseline-shift="-1ex" dominant-baseline="middle" >'.$i.'</text>';
				}
				//Horizontal grid
				$gridH .= "\t\t".'<path d="M 50 '.$stepY.' H '.($width+50).'"/>'."\n" ;
			}
		}
		$y .= "\t".'</g>'."\n";
		return array('y'=>$y,'gridH'=>$gridH);
	}

	/**
	 * To draw legends under the grid
	 * @param $legends array Array of legends
	 * @param $type string type of graph
	 * @param $stroke string color of legends
	 * @param $HEIGHT integer height of the entire svg tag
	 * @param $paddingTop integer padding on top
	 * @return string path of legends
	 *
	 * @author Cyril MAGUIRE
	 */
	protected function __legendsDef($legends,$type,$stroke,$HEIGHT,$paddingTop) {
		if (isset($legends) && !empty($legends)) {
			$leg = "\n\t".'<g class="graph-legends">';
			if (!is_array($legends)) {
				$legends = array(0 => $legends);
			}
			foreach ($legends as $key => $value) {
				if (isset($type[$key]) && $type[$key] != 'pie' && $type[$key] != 'ring') {
					if (is_array($stroke) && isset($stroke[$key])) {
						$leg .= "\n\t\t".'<rect x="50" y="'.($HEIGHT+30+$key*(2*$paddingTop)).'" width="10" height="10" fill="'.$stroke[$key].'" class="graph-legend-stroke"/>';
					} else {
						$leg .= "\n\t\t".'<rect x="50" y="'.($HEIGHT+30+$key*(2*$paddingTop)).'" width="10" height="10" fill="'.$stroke.'" class="graph-legend-stroke"/>';
					}
					$leg .= "\n\t\t".'<text x="70" y="'.($HEIGHT+40+$key*(2*$paddingTop)).'" text-anchor="start" class="graph-legend">'.$value.'</text>';
				}
				if (is_array($type) && (in_array('stock', $type) || in_array('h-stock', $type))) {
					if (is_array($stroke)) {
						$stroke = array_values($stroke);
						if(isset($stroke[$key+1])) {
							$leg .= "\n\t\t".'<rect x="50" y="'.($HEIGHT+30+$key*(2*$paddingTop)).'" width="10" height="10" fill="'.$stroke[$key+1].'" class="graph-legend-stroke"/>';
						}
					}
					$leg .= "\n\t\t".'<text x="70" y="'.($HEIGHT+40+$key*(2*$paddingTop)).'" text-anchor="start" class="graph-legend">'.$value.'</text>';
				}
			}
			$leg .= "\n\t".'</g>';

		} else {
			$leg = '';
		}
		return $leg;
	}

	/**
	 * To generate hexadecimal code for color
	 * @param null
	 * @return string hexadecimal code
	 *
	 * @author Cyril MAGUIRE
	 */
	protected function __genColor() {
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

	protected function __stockDef() {
		$return = "\n\t".'<defs>';
		$return .= "\n\t\t".'<g id="plotLimit">';
		$return .= "\n\t\t\t".'<path d="M 0 0 L 10 0" class="graph-line" stroke="" stroke-opacity="1"/>';
		$return .= "\n\t\t".'</g>';
		$return .= "\n\t".'</defs>'."\n";
		return $return;
	}

	protected function __hstockDef() {
		$return = "\n\t".'<defs>';
		$return .= "\n\t\t".'<g id="plotLimit">';
		$return .= "\n\t\t\t".'<path d="M 0 0 V 0 10" class="graph-line" stroke="" stroke-opacity="1"/>';
		$return .= "\n\t\t".'</g>';
		$return .= "\n\t".'</defs>'."\n";
		return $return;
	}

	protected function __gradientDef($gradient,$id) {
		$return ="\n\t".'<defs>';
		$return .= "\n\t\t".'<linearGradient id="'.$id.'">';
		$return .= "\n\t\t\t".'<stop offset="5%" stop-color="'.$gradient[0].'" />';
		$return .= "\n\t\t\t".'<stop offset="95%" stop-color="'.$gradient[1].'" />';
		$return .= "\n\t\t".'</linearGradient>';
		$return .= "\n\t".'</defs>'."\n";
		return $return;
	}

	protected function __header($dimensions=array('width'=>'100%','height'=>'100%','widthViewBox'=>685,'heightViewBox'=>420), $responsive=true,$id=false) {
		if ($responsive == true) {
			$return = "\n".'<svg xmlns="http://www.w3.org/2000/svg" version="1.1" xml:lang="fr" xmlns:xlink="http://www.w3/org/1999/xlink" class="graph" width="100%" height="100%" viewBox="0 0 '.($dimensions['widthViewBox']).' '.($dimensions['heightViewBox']).'" preserveAspectRatio="xMidYMid meet"'.($id ? ' id="'.$id.'"':'').'>'."\n";
		} else {
			$return = "\n".'<svg xmlns="http://www.w3.org/2000/svg" version="1.1" xml:lang="fr" xmlns:xlink="http://www.w3/org/1999/xlink" class="graph" width="'.$dimensions['width'].'" height="'.$dimensions['height'].'" viewBox="0 0 '.$dimensions['widthViewBox'].' '.($dimensions['heightViewBox']).'" preserveAspectRatio="xMidYMid meet"'.($id ? ' id="'.$id.'"':'').'>'."\n";
		}
		$return .= "\n".'<defs>
	    <style type="text/css"><![CDATA[
	      '.$this->css.'
	    ]]></style>'."\n".'</defs>'."\n";
  		return $return;
	}

	protected function __svgGrid($gradient,$width,$height,$yHeight) {
		$return = '';
		$background = '#ffffff';
		if (is_array($gradient) && count($gradient) == 2) {
			$id = 'BackgroundGradient'.rand();
			$return .= $this->__gradientDef($gradient,$id);
			$background = 'url(#'.$id.')';
		}
		//Grid is beginning at 50 units from the left
		return $return .= "\t".'<rect x="50" y="'.$yHeight.'" width="'.$width.'" height="'.$height.'" class="graph-stroke" fill="'.$background.'" fill-opacity="1"/>'."\n";
	}

	protected function __titleDef($title,$width,$titleHeight) {
		$return = "\t".'<title class="graph-tooltip">'.$title.'</title>'."\n";
		$return .= "\t".'<text x="'.(($width/2)+50).'" y="'.$titleHeight.'" text-anchor="middle" class="graph-title">'.$title.'</text>'."\n";
		return $return;
	}

	protected function __headerDimensions($widthViewBox,$HEIGHT,$heightLegends,$titleHeight,$paddingTop,$paddingLegendX,$lenght,$stepX) {
		return array(
			'widthViewBox' => $widthViewBox,
			'heightViewBox' => $HEIGHT+$heightLegends+$titleHeight+2*$paddingTop+$paddingLegendX,
			'width' => $lenght*$stepX+$stepX,
			'height' => $HEIGHT+$heightLegends+$titleHeight+2*$paddingTop,
		);
	}

	protected function __c($coordonnees,$stroke) {
		return "\n\t\t\t".'<circle '.$coordonnees.' r="3" stroke="'.$stroke.'" class="graph-point-active"/>';
	}

	/**
	* Méthode permettant de trier un tableau multidimensionnel sur un ou deux index
	* @param $array array le tableau à trier
	* @param $keys mixed (array ou string) l'index ou le tableau des deux index sur le(s)quel(s) va(vont) se faire le tri
	* 
	* @return array
	* 
	* @author Cyril MAGUIRE
	*/	
    public function arraySort(&$array,$keys) {
    	if (!is_array($keys)) $keys = array(0=>$keys);
    	$c = count($keys);
		$cmp = function ($a, $b) use ($keys,$c) {
			$i = 0;
	    	if ($c>1){  
				if (strcasecmp($a[$keys[$i]], $b[$keys[$i]]) == 0){
					if (isset($keys[$i+1]) && isset($a[$keys[$i+1]])){
						return strcasecmp($a[$keys[$i+1]], $b[$keys[$i+1]]);
					} else {
						return strcasecmp($a[$keys[$c-1]], $b[$keys[$c-1]]);
					}
				} else {
					return strcasecmp($a[$keys[$i]], $b[$keys[$i]]);
					}
			} else {
				return strcasecmp($a[$keys[0]], $b[$keys[0]]);
			}
	    };
	    return usort($array,$cmp);
	}

	/**
	 * Transform svg file into vml file for internet explorer
	 *
	 * @param $svg string svg to transform
	 * @param $vml string path to vml file
	 * @param $root string path of root of app
	 * @param $url string url of site
	 * @return vml string
	 *
	 * @author Cyril MAGUIRE
	 */
	public function svg2vml($svg,$vml,$root,$xsl='vendors/svg2vml/svg2vml.xsl',$xslpath='/svg2vml/') {
		include_once 'svg2vml/xslt.php';
		if(is_string($svg)){
			$xsl = str_replace('include href="XSL2', 'include href="'.$xslpath.'XSL2', file_get_contents($xsl));
			# for $xsl, see http://vectorconverter.sourceforge.net/index.html
			$xml_contents=$svg;
			$from="/(<meta[^>]*[^\/]?)>/i";
			$xml_contents=preg_replace($from,"$1/>",$xml_contents);
			$from="/\/(\/>)/i";
			$xml_contents=preg_replace($from,"$1",$xml_contents);
			$xml_contents=preg_replace("/<\!DOCTYPE[^>]+\>/i","",$xml_contents);	
			$xml_contents=preg_replace("/<\?xml-stylesheet[^>]+\>/i","",$xml_contents);
			$xml_contents=preg_replace("/(\r\n|\n|\r)/s", '', $xml_contents);
			$xml_contents=str_replace(array("\r\n","\n","\r",CHR(10),CHR(13)), '', trim($xml_contents));
			$xml_contents=preg_replace("/\<defs\>(\s*)\<style(.*)\<\/style\>(\s*)\<\/defs\>/", "", $xml_contents);

			$xh=xslt_create();
			$arguments=array('/_xml' =>$xml_contents,'/_xsl' => $xsl);
			$result=xslt_process($xh, 'arg:/_xml', 'arg:/_xsl', NULL, $arguments);
			xslt_free($xh);

			if ($result) {
			    $result = str_replace('<?xml version="1.0"?>'."\n", '', $result);
			    $result = str_replace('><',">\n<",$result);
    			file_put_contents($root.$vml, $result);
    			$output = "<div class=\"object\"><object type=\"text/html\" data=\"".$root.$vml."\" >";
    			$output .= "</object></div>\n";
    			$output = $this->wmodeTransparent($output);
    			return $output;
			} else {
				$f = str_replace(array('.html',PLX_PHPGRAPH),array('.png',PLX_PHPGRAPH_IMG),$root.$vml);
				if (is_file($f)) {
					$plxMotor = plxMotor::getInstance();
					return '<img src="'.$plxMotor->urlRewrite($f).'" alt="graph" />';
				}
			}
		} else{
			return L_ERROR_FILE_NOT_FOUND;
		}
	}

	/**
	 * Méthode pour prendre en compte le mode transparent des iframes
	 *
	 * @parm	html	chaine de caractères à scanner
	 * @return	string	chaine de caractères modifiée
	 * @author	Stephane F
	 **/
	public function wmodeTransparent($html) {

		if(strpos($html, "<embed src=" ) !== false) {
			return str_replace('</param><embed', '</param><param name="wmode" value="transparent"></param><embed wmode="transparent" ', $html);
		} elseif(strpos($html, 'feature=oembed') !== false) {
			return str_replace('feature=oembed', 'feature=oembed&amp;wmode=transparent', $html);
		} else {
			return $html;
		}
	}

	/**
	 * Put svg file in cache
	 *
	 * @param $svg string svg to record
	 * @param $outputName string name of svg file
	 * @param $outputDir string path of cache directory
	 * @return bool
	 *
	 * @author Cyril MAGUIRE
	 */
	protected function putInCache($svg,$outputName='svg',$outputDir='data/img/') {
		if (!is_dir($outputDir)) {
			mkdir($outputDir);
		}
		// To avoid multiple file in a directory, we delete all (I know it's brutal)
		$files = glob($outputDir.'*.svg');
		foreach ($files as $key => $file) {
			unlink($file);
		}
		// Then, we record only one file
		file_put_contents($outputDir.$outputName.'.svg', $svg);
	}

	# Work in progress......
	public function svgToPng($svg,$outputName='svg',$outputDir='data/img/',$width=800,$height=600) {
		// exit();
		exec("convert -version", $out);//On teste la présence d'imagick sur le serveur
		if (!empty($out)) {
			$im = new Imagick();
			$imagick->setBackgroundColor(new ImagickPixel('transparent'));
			$im->readImageBlob($svg);
			/*png settings*/
			$im->setImageFormat("png24");
			$im->resizeImage($width, $height, imagick::FILTER_LANCZOS, 1);  /*Optional, if you need to resize*/

			/*jpeg*/
			$im->setImageFormat("jpeg");
			$im->adaptiveResizeImage($width, $height); /*Optional, if you need to resize*/

			$im->writeImage(SIG_ROOT.$outputDir.$outputName.'.png');
			$im->writeImage(SIG_ROOT.$outputDir.$outputName.'.jpg');
			$im->clear();
			$im->destroy();
			echo '<img src="'.Router::url($outputDir.$outputName.'.png').'" alt="'.$outputName.'.png" />';
		}
	}
}
?>
