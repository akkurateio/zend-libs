<?php

namespace Subvitamine\Libs;

/**
 * Classe offrant un pannel de fonctions sur les graphiques   
 */
class LibGraph {
	/**
	 * Traduction de highCharts en français
	 */
	public static function highchartsFrenchTranslation()
	{
		return "Highcharts.setOptions({
		    lang: {
		        months: ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'],
				weekdays: ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'],
				shortMonths: ['Jan', 'Fev', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Aout', 'Sept', 'Oct', 'Nov', 'Déc'],
				decimalPoint: ',',
				downloadPNG: 'Télécharger en image PNG',
				downloadJPEG: 'Télécharger en image JPEG',
				downloadPDF: 'Télécharger en document PDF',
				downloadSVG: 'Télécharger en document Vectoriel',
				exportButtonTitle: 'Export du graphique',
				loading: 'Chargement en cours...',
			 	printButtonTitle: 'Imprimer le graphique',
				resetZoom: 'Réinitialiser le zoom',
				resetZoomTitle: 'Réinitialiser le zoom au niveau',
				thousandsSep: ' ',
				decimalPoint: ',',
				contextButtonTitle: 'Menu d\'impression',
				printChart: 'Imprimer le graphique',
				noData: 'Aucune donnée à afficher',
				drillUpText: 'Revenir'
		    }
		});";
	}

    /**
     * Création d'un cammembert avec catégories
     * Syntaxe du tableau attendu : 
     * array (
     *   'categorie 1' => array(
     *     'nb' => nbQueries,
     *     'data' => array(
     *       'ip 1' => nbQueries,
     *       'ip 2' => nbQueries,
     *       ...
     *     )      
     *   ),
     *   'categorie 2 => array(
     *       ...
     *   ),
     *   ...
     * )
     * @param array $data
     * @param string $title
     * @param string $subtitle
     * @param string $container
     * @param string $unit
     * @return string
     */
    static public function computeDonutChart($data, $title, $subtitle, $container, $unit = '') {

        // On extrait les catégories
        $categories = implode(',', array_map('Subvitamine\Libs\LibString::escape', array_keys($data)));

        $html = <<< JS

    var chart;
    var colors = Highcharts.getOptions().colors,
        categories = [$categories],
        name = '$title',
        data = [
JS;

        //On construit les données pour chacune des catégories
        $count = 0;
        $all = 0;
        foreach ($data as $categorie => $d) {
            $nb = $d['nb'];
            $all += $nb;
            $subcategories = implode(',', array_map('Subvitamine\Libs\LibString::escape', array_keys($d['data'])));
            $subcategories_value = implode(',', array_map('intval', array_values($d['data'])));

            $html .= <<< JS
            {
                y: $nb,
                color: colors[$count],
                drilldown: {
                    name: '$categorie detail',
                    categories: [$subcategories],
                    data: [$subcategories_value],
                    color: colors[$count]
                }
            },
JS;
            ++$count;
        }

        if ($all === 0) {
            return array('status' => false,
                'result' => 'No Data !');
        }

        // On supprime la dernière virgule inutile
        $html = substr($html, 0, -1);

        $html .= <<< JS
    ];


    // Build the data arrays
    var stats = [];
    var versionsData = [];
    for (var i = 0; i < data.length; i++) {

        // add categories data
        stats.push({
            name: categories[i],
            y: data[i].y,
            color: data[i].color
        });

        // add data
        for (var j = 0; j < data[i].drilldown.data.length; j++) {
            var brightness = 0.2 - (j / data[i].drilldown.data.length) / 5 ;
            versionsData.push({
                name: data[i].drilldown.categories[j],
                y: data[i].drilldown.data[j],
                color: Highcharts.Color(data[i].color).brighten(brightness).get()
            });
        }
    }

    // Create the chart
    chart = new Highcharts.Chart({
        chart: {
            renderTo: '$container',
            type: 'pie'
        },
        title: {
            text: '$title'
        },
        subtitle: {
                text: '$subtitle',
                x: -20
        },
        yAxis: {
            title: {
                text: 'Total'
            }
        },
        plotOptions: {
            pie: {
                shadow: false
            }
        },
        tooltip: {
            formatter: function() {
                return '<b>'+ this.point.name +'</b>: '+ this.y + '$unit';
            }
        },
        series: [{
            name: '$title',
            data: stats,
            size: '60%',
            dataLabels: {
                formatter: function() {
                    return this.y > 5 ? this.point.name : null;
                },
                color: 'white',
                distance: -30
            }
        }, {
            name: 'Versions',
            data: versionsData,
            innerSize: '60%',
            dataLabels: {
                formatter: function() {
                    // display only if larger than 1
                    return this.y > 1 ? '<b>'+ this.point.name +':</b> '+ this.y + '$unit' : null;
                }
            }
        }]
    });
JS;

        return array('status' => true,
            'result' => $html);
    }

    /**
     * Création d'une courbe
     * * Syntaxe du tableau attendu : 
     * array (
     *   'categorie 1' => array(
     *     'nb' => nbQueries,
     *     'data' => array(
     *       'ip 1' => nbQueries,
     *       'ip 2' => nbQueries,
     *       ...
     *     )      
     *   ),
     *   'categorie 2 => array(
     *       ...
     *   ),
     *   ...
     * )
     * @param array $data
     * @param string $title
     * @param string $subtitle
     * @param string $yTitle
     * @param string $container
     * @param string $unit
     * @return string
     */
    static public function computeBasicLine($data, $title, $subtitle, $yTitle, $container, $unit = '') {
        if (empty($data)){
            return array('status' => false,
            'result' => 'No Data');
        }
        
        // On extrait les catégories
        // On récupère le premier élément
        $current = current($data);
        $categories = implode(',', array_map('Subvitamine\Libs\LibString::escape', array_keys($current['data'])));

        $html = <<< JS

    var chart;
    chart = new Highcharts.Chart({
		chart: {
			renderTo: '$container',
			type: 'area',
                        zoomType: 'x',
                        spacingRight: 20,
                        backgroundColor:'rgba(255, 255, 255, 0.1)'
		},
		title: {
			text: '$title',
			x: -20 //center
		},
		subtitle: {
			text: '$subtitle',
			x: -20
		},
		xAxis: {
			categories: [$categories]
		},
		yAxis: {
			title: {
				text: '$yTitle'
			},
			plotLines: [{
				value: 0,
				width: 1,
				color: '#808080'
			}]
		},
		tooltip: {
			formatter: function() {
					return '<b>'+ this.series.name +'</b><br/>'+
					this.x +' : '+ this.y +'$unit';
			}
		},
		legend: {
			enabled: true
		},
                plotOptions: {
                    series: {
                        lineWidth: 1,
                        shadow: false,
                        fillColor: {
                            linearGradient: [0, 0, 0, 300],
                            stops: [
                                [0, Highcharts.getOptions().colors[0]],
                                [1, 'rgba(2,0,0,0)']
                            ]
                        },
                        marker: {
                            enabled: false,
                            states: {
                                hover: {
                                    enabled: true
                                }
                            }
                        }
                    }
                },
		series: [
JS;
        $all = 0;
        foreach ($data as $categorie => $d) {
            $nb = $d['nb'];
            $all += $nb;

            $subcategories_value = implode(',', array_values($d['data']));

            $html .=<<<JS
                {
                        type: 'area',
			name: '$categorie',
			data: [$subcategories_value]
		},
JS;
        }

        if ($all === 0) {
            return array('status' => false,
                'result' => 'No Data !');
        }

        // On supprime la dernière virgule inutile
        $html = substr($html, 0, -1);


        $html .=<<<JS
    ]
	});
JS;

        return array('status' => true,
            'result' => $html);
    }

    /**
     * Création d'une courbe de temps
     * Syntaxe du tableau attendu : 
     * array (
     *   'categorie 1' => array(
     *     'nb' => nbQueries,
     *     'data' => array(
     *       'date 1' => nbQueries,
     *       'date 2' => nbQueries,
     *       ...
     *     )      
     *   ),
     *   'categorie 2 => array(
     *       ...
     *   ),
     *   ...
     * )
     * @param array $data
     * @param string $title
     * @param string $subtitile
     * @param string $yTitle
     * @param string $container
     * @param int $slot
     * @param string $unit
     * @return string
     */
    static public function computeTimeSeries($data, $title, $subtitile, $yTitle, $container, $slot, $unit = '') {
        if (empty($data)){
            return array('status' => false,
            'result' => 'No Data');
        }
        
        //Calcul de l'interval en fonction du slot
        switch ($slot) {
            case 1 :
                $interval = '1/2 * 3600 * 1000';  //30 minutes
                $maxZoom = '1/2 * 24 * 3600000'; // demi journée
                break;
            case 2 :
                $interval = '3600 * 1000';    //1 heure
                $maxZoom = '1/2 * 24 * 3600000'; // demi journée
                break;
            case 3 :
                $interval = '24 * 3600 * 1000';   //1 journée
                $maxZoom = '7 * 24 * 3600000'; // semaine
                break;
            case 4 :
                $interval = '7 * 24 * 3600 * 1000';  //1 semaine
                $maxZoom = '31 * 24 * 3600000'; // 1 mois
                break;
            case 5 :
                $interval = '31 * 24 * 3600 * 1000'; //1 mois
                $maxZoom = '31 * 6 * 24 * 3600000'; // 1/2 année
                break;
            default: 
                $interval = '3600 * 1000';    //1 heure
                $maxZoom = '1/2 * 24 * 3600000'; // demi journée
                break;
        }

        $html = <<<JS
        var chart;
        chart = new Highcharts.Chart({
        chart: {
            renderTo: '$container',
            zoomType: 'x',
            spacingRight: 20
        },
        title: {
            text: '$title'
        },
        subtitle: {
            text: document.ontouchstart === undefined ?
                '$subtitile' :
                '$subtitile'
        },
        xAxis: {
            type: 'datetime',
            maxZoom: $maxZoom,
            title: {
                text: null
            }
        },
        yAxis: {
            title: {
                text: '$yTitle'
            },
            min: 0,
            startOnTick: false,
            showFirstLabel: false
        },
        tooltip: {
            shared: true,
            xDateFormat: '%d/%m/%Y %H:%M'
        },
        legend: {
            enabled: true
        },
        plotOptions: {
            area: {
                fillColor: {
                    linearGradient: [0, 0, 0, 300],
                    stops: [
                        [0, Highcharts.getOptions().colors[0]],
                        [1, 'rgba(2,0,0,0)']
                    ]
                },
                lineWidth: 1,
                marker: {
                    enabled: false,
                    states: {
                        hover: {
                            enabled: true,
                            radius: 5
                        }
                    }
                },
                shadow: false,
                states: {
                    hover: {
                        lineWidth: 1
                    }
                }
            }
        },

        series: [
JS;

        $all = 0;
        foreach ($data as $categorie => $d) {
            $nb = $d['nb'];
            $all += $nb;
            $date_begin = key($d['data']);
            $date_begin = str_replace('/', '-', $date_begin);
            $date_begin = strtotime($date_begin);
            $y = date('Y', $date_begin);
            $m = intval(date('m', $date_begin));
            --$m;
            $day = date('d', $date_begin);

            $data = implode(',', array_map('intval', array_values($d['data'])));


            $html .=<<<JS
   {
        type: 'area',
        name: '$categorie',
        pointInterval: $interval,
        pointStart: Date.UTC($y, $m, $day),
        data: [$data]
   },
JS;
        }

        if ($all === 0) {
            return array('status' => false,
                'result' => 'No Data !');
        }

        // On supprime la dernière virgule inutile
        $html = substr($html, 0, -1);
        $html .= "]});";

        return array('status' => true,
            'result' => $html);
    }
    
    /**
     * Création d'un histogramme
     * Syntaxe du tableau attendu : 
     * array (
     *   'abscisse 1' => array(
     *     'data' => array(
     *       'serie 1' => nbQueries,
     *       'serie 2' => nbQueries,
     *       ...
     *     )      
     *   ),
     *   'abscisse 2 => array(
     *       ...
     *   ),
     *   ...
     * )
     * @param array $data
     * @param string $title
     * @param string $subtitle
     * @param string $yTitle
     * @param string $container
     * @param string $unit
     * @param array $listCategorie
     * @return string
     */
    static public function computeBasicColumn($data, $title, $subtitle, $yTitle, $container, $unit = '', $listCategorie = array()) {
        // On extrait les catégories
        // On récupére le premier élément
        $listCategorie = utf8_encode(implode(', ', array_map('Subvitamine\Libs\LibString::escape', $listCategorie)));
	
        $html = <<<JS
        var chart;
	chart = new Highcharts.Chart({
		chart: {
			renderTo: '$container',
			type: 'column',
                        zoomType: 'x'
		},
		title: {
			text: '$title'
		},
		subtitle: {
			text: '$subtitle'
		},
		xAxis: {
			categories: [$listCategorie],
		},
		yAxis: {
			min: 0,
			title: {
				text: '$yTitle'
			}
		},
		legend: {
			layout: 'horizontal',
			backgroundColor: '#FFFFFF',
			shadow: true
		},
		tooltip: {
			formatter: function() {
				return ''+
					this.x + ' (' + this.series.name + ') ' +': '+ this.y +' $unit';
			}
		},
		plotOptions: {
			column: {
				pointPadding: 0.2,
				borderWidth: 0
			}
		},
			series: [
JS;

        // On reconstitue le tableau
        $new_data = array();
        $data_count = 0;
        foreach ($data as $cat => $d) {
            foreach ($d['data'] as $subcat => $val) {
                $data_count += $val;
                $new_data[$subcat][$cat] = $val;
            }
        }

        if ($data_count === 0) {
            return array('status' => false,
                'result' => 'Aucune donnée à afficher !');
        }

        //On fourni les résultats au graph
        foreach ($new_data as $subcat => $nd) {
            $values = implode(',', array_map('intval', array_values($nd)));
            $html .=<<<JS
   {
        name: '$subcat',
        data: [$values]
   },
JS;
        }

        // On supprime la dernière virgule inutile
        $html = substr($html, 0, -1);

        $html .=<<<JS
        ]
	});
JS;

        return array('status' => true,
            'result' => $html);
    }

    /**
     * Création de colonnes à type de valeurs "normales"
     * Syntaxe du tableau attendu : 
     * array (
     *   'categorie 1' => array(
     *     'nb' => nbQueries,
     *     'data' => array(
     *       'ip 1' => nbQueries,
     *       'ip 2' => nbQueries,
     *       ...
     *     )      
     *   ),
     *   'categorie 2 => array(
     *       ...
     *   ),
     *   ...
     * )
     * @param array $data
     * @param string $title
     * @param string $subtitile
     * @param string $yTitle
     * @param string $container
     * @param string $unit
     * @param int $rotation
     * @param int $max
     * @param int $tickInterval
     * @return string
     */
    static public function computeStackedColumn($data, $title, $subtitile, $yTitle, $container, $unit = '', $rotation = 0, $max = null, $tickInterval = null) {
        return self::computeGenericStackedColumn('normal', $data, $title, $subtitile, $yTitle, $container, $unit, $rotation, $max, $tickInterval);
    }
    
    /**
     * Création de colonnes à valeurs "pourcentages"
     * Syntaxe du tableau attendu : 
     * array (
     *   'categorie 1' => array(
     *     'nb' => nbQueries,
     *     'data' => array(
     *       'ip 1' => nbQueries,
     *       'ip 2' => nbQueries,
     *       ...
     *     )      
     *   ),
     *   'categorie 2 => array(
     *       ...
     *   ),
     *   ...
     * )
     * @param array $data
     * @param string $title
     * @param string $subtitile
     * @param string $yTitle
     * @param string $container
     * @param string $unit
     * @param int $rotation
     * @return string
     */
    static public function computeStackedPercentageColumn($data, $title, $subtitile, $yTitle, $container, $unit = '', $rotation = 0) {
        $tab = array_values(current($data));
        $categories = array_keys($tab[1]);
        $categories = implode("', '", $categories);
        $categories = "['" . $categories . "']";

        $html = <<<JS
        var chart;
        chart = new Highcharts.Chart({
        chart: {
            renderTo: '$container',
            zoomType: 'x',
            type: 'column'
        },
        title: {
            text: '$title'
        },
        subtitle: {
            text: document.ontouchstart === undefined ?
                '$subtitile' :
                '$subtitile'
        },
        xAxis: {
            categories: $categories,
	    labels: {
                rotation: $rotation,
                align: 'right',
                style: {
                    fontFamily: 'Verdana, sans-serif'
                }
            }
        },
        yAxis: {
            title: {
                text: '$yTitle'
            },
            min: 0
        },
        tooltip: {
            formatter: function() {
				return ''+
					this.series.name +': '+ this.y +' ('+ Math.round(this.percentage) +'%)';
			}
        },
        legend: {
            enabled: true
        },
        plotOptions: {
			column: {
				stacking: 'percent'
			}
		},
        series: [
JS;

        $all = 0;
        foreach ($data as $categorie => $d) {
            $nb = $d['nb'];
            $all += $nb;
            $date_begin = key($d['data']);
            $date_begin = str_replace('/', '-', $date_begin);
            $date_begin = strtotime($date_begin);
            $y = date('Y', $date_begin);
            $m = intval(date('m', $date_begin));
            --$m;
            $day = date('d', $date_begin);

            $data = implode(',', array_map('intval', array_values($d['data'])));


            $html .=<<<JS
   {
       	name: '$categorie',
       	data: [$data]
   },
JS;
        }

        if ($all === 0) {
            return array('status' => false,
                'result' => 'Aucune donnée à afficher !');
        }

        // On supprime la derniÃ¨re virgule inutile
        $html = substr($html, 0, -1);
        $html .= "]});";

        return array('status' => true,
            'result' => $html);
    }

    /**
     * Création de colonnes à type de valeurs variable
     * Syntaxe du tableau attendu : 
     * array (
     *   'categorie 1' => array(
     *     'nb' => nbQueries,
     *     'data' => array(
     *       'ip 1' => nbQueries,
     *       'ip 2' => nbQueries,
     *       ...
     *     )      
     *   ),
     *   'categorie 2 => array(
     *       ...
     *   ),
     *   ...
     * )
     * @param string $type
     * @param array $data
     * @param string $title
     * @param string $subtitile
     * @param string $yTitle
     * @param string $container
     * @param string $unit
     * @param int $rotation
     * @param int $max
     * @param int $tickInterval
     * @return string
     */
    static protected function computeGenericStackedColumn($type, $data, $title, $subtitile, $yTitle, $container, $unit = '', $rotation = 0, $max = null, $tickInterval = null) {
        if (empty($data)){
            return array('status' => false,
            'result' => 'No Data');
        }

        $tab = array_values(current($data));
        $categories = array_keys($tab[1]);
        $categories = implode("', '", $categories);
        $categories = "['" . $categories . "']";
        
        $uniqueId = time();
        
        $maxOption = null;
        if($max != null)
        {
        	$maxOption = 'max: '.(int) $max.',';
        }
        
        $tickOption = null;
        if($tickInterval != null)
        {
        	$tickOption = 'tickInterval: '.(int) $tickInterval.',';
        }

        $html = <<<JS
        var chart$uniqueId;
        chart$uniqueId = new Highcharts.Chart({
        chart: {
            renderTo: '$container',
            zoomType: 'x',
            type: 'column',
            backgroundColor:'rgba(255, 255, 255, 0.1)'
        },
        title: {
            text: '$title'
        },
        subtitle: {
            text: document.ontouchstart === undefined ?
                '$subtitile' :
                '$subtitile'
        },
        xAxis: {
            categories: $categories,
            labels: {
                rotation: $rotation,
                align: 'right',
                style: {
                    fontFamily: 'Verdana, sans-serif'
                }
            }
        },
        yAxis: {
            title: {
                text: '$yTitle'
            },
            min: 0,
			$maxOption
			$tickOption
            type: 'category'
        },
        tooltip: {
            formatter: function() {
				return ''+
JS;
        if ($unit === ''){
            $html .= "this.series.name +': '+ this.y;";
        } else {
            $html .= "this.series.name +': '+ this.y +' $unit';";
        }
$html .=<<<JS
					this.series.name +': '+ this.y +' ('+ Math.round(this.percentage) +'%)';
			}
        },
        legend: {
            enabled: true
        },
        plotOptions: {
			column: {
				stacking: '$type'
			}
		},
        series: [
JS;

        $all = 0;
        foreach ($data as $categorie => $d) {
        	$categorie = addslashes($categorie);
            $nb = $d['nb'];
            $all += $nb;

            $data = implode(',', array_map('intval', array_values($d['data'])));


            $html .=<<<JS
   {
       	name: '$categorie',
       	data: [$data]
   },
JS;
        }

        if ($all === 0) {
            return array('status' => false,
                'result' => 'No Data !');
        }

        // On supprime la dernière virgule inutile
        $html = substr($html, 0, -1);
        $html .= "]});";

        return array('status' => true,
            'result' => $html);
    }
    
    /**
     * Création d'un cammembert
     * Syntaxe du tableau attendu : 
     * array (
     *   'categorie 1' => array(
     *     'nb' => nbQueries)      
     *   ),
     *   'categorie 2' => array(
     *     'nb' => nbQueries)      
     *   ),
     *   ...
     * )
     * @param array $data
     * @param string $title
     * @param string $subtitle
     * @param string $container
     * @param string $unit
     * @return string
     */
    static public function computePieChart($data, $title, $subtitle, $container, $unit = '') {

        // On extrait les catégories
        $categories = implode(',', array_map('Subvitamine\Libs\LibString::escape', array_map('addslashes', array_keys($data))));

        $html = <<< JS

    var chart;
    var colors = Highcharts.getOptions().colors,
        categories = [$categories],
        name = '$title',
        data = [
JS;

        //On construit les données pour chacune des catégories
        $count = 0;
        foreach ($data as $categorie => $d) {
            $nb = round($d['nb'], 2);
            $name = addslashes($categorie);

            $html .= <<< JS
            {
                y: $nb,
                color: colors[$count],
                drilldown: {
                    name: '$name detail',
                    color: colors[$count]
                }
            },
JS;
            ++$count;
        }

        // On supprime la dernière virgule inutile
        $html = substr($html, 0, -1);

        $html .= <<< JS
    ];


    // Build the data arrays
    var stats = [];
    var versionsData = [];
    for (var i = 0; i < data.length; i++) {

        // add categories data
        stats.push({
            name: categories[i],
            y: data[i].y,
            color: data[i].color
        });
    }

    // Create the chart
    chart = new Highcharts.Chart({
        chart: {
            renderTo: '$container',
            type: 'pie'
        },
        title: {
            text: '$title'
        },
        credits: {
                enabled: false
        },  
        subtitle: {
                text: '$subtitle',
                x: -20
        },
        yAxis: {
            title: {
                text: 'Total'
            }
        },
        plotOptions: {
            pie: {
                shadow: false,
                allowPointSelect: true
            }
        },
        tooltip: {
            formatter: function() {
                return '<b>'+ this.point.name +'</b>: '+ this.y + '$unit';
            }
        },
        series: [{
            name: '$title',
            data: stats,
            size: '74%',
            dataLabels: {
                    enabled: true,
                    color: '#000000',
                    connectorColor: '#000000',
                    formatter: function() {
                        return '<b>'+ this.point.name +'</b>: '+ this.y + '$unit';
                    }
            }
        }]
    });
JS;

        return array('status' => true,
            'result' => $html);
    }
    
    /**
     * Création d'une courbe de temps avec interval irregulier
     * Syntaxe du tableau attendu : 
     * array (
     *   'categorie 1' => array(
     *     'nb' => nbQueries,
     *     'data' => array(
     *       'date 1' => nbQueries,
     *       'date 2' => nbQueries,
     *       ...
     *     )      
     *   ),
     *   'categorie 2 => array(
     *       ...
     *   ),
     *   ...
     * )
     * @param array $data
     * @param string $title
     * @param string $subtitile
     * @param string $yTitle
     * @param string $container
     * @param int $slot
     * @param string $unit
     * @return string
     */
    static public function computeTimeIrregularIntervals($data, $title, $subtitile, $yTitle, $container, $max = null, $unit = '', $type = 'area', $dateFormat = '%e. %b %H:%M', $tickInterval = null) {
        if (empty($data)){
            return array('status' => false,
            'result' => 'No Data');
        }
        
        $maxOption = null;
        if(!empty($max))
        {
        	$maxOption = "max: ".$max.",";
        }
        
        $tickOption = null;
        if($tickInterval != null)
        {
        	$tickOption = 'tickInterval: '.(int) $tickInterval.',';
        }
        
		$maxZoom = '1/2 * 24 * 3600000';

        $html = <<<JS
        var chart;
        chart = new Highcharts.Chart({
        chart: {
            renderTo: '$container',
            zoomType: 'x',
            spacingRight: 20,
	    type: 'spline'
        },
        title: {
            text: '$title'
        },
        subtitle: {
            text: document.ontouchstart === undefined ?
                '$subtitile' :
                '$subtitile'
        },
        xAxis: {
            type: 'datetime',
            maxZoom: $maxZoom,
            title: {
                text: null
            }
        },
        yAxis: {
            title: {
                text: '$yTitle'
            },
            min: 0,
            startOnTick: false,
            $maxOption
            $tickOption
            showFirstLabel: false
        },
        tooltip: {
	    formatter: function() {
                        return '<b>'+ this.series.name +'</b><br/>'+
                        Highcharts.dateFormat('$dateFormat', this.x) +': '+ this.y+' $unit';
                }
        },
        legend: {
            enabled: true
        },
	plotOptions: {
            area: {
                fillColor: {
                    linearGradient: [0, 0, 0, 300],
                    stops: [
                        [0, Highcharts.getOptions().colors[0]],
                        [1, 'rgba(2,0,0,0)']
                    ]
                },
                lineWidth: 1,
                marker: {
                    enabled: false,
                    states: {
                        hover: {
                            enabled: true,
                            radius: 5
                        }
                    }
                },
                shadow: false,
                states: {
                    hover: {
                        lineWidth: 1
                    }
                }
            }
        },
        series: [
JS;

        $all = 0;
        foreach ($data as $categorie => $d) {
	    if(count($d['data']) > 0)
	    {
		$nb = $d['nb'];
		$all += $nb;
		$html .=<<<JS
   {
        name: '$categorie',
		type: '$type',
        data: [
JS;
		foreach($d['data'] as $date => $value)
		{
		    $timestamp = strtotime($date);
		    $year = date('Y', $timestamp);
		    $month = date('m', $timestamp) - 1;
		    $day = date('d', $timestamp);
		    $hour = date('H', $timestamp);
		    $min = date('i', $timestamp);
		    $dateForUtc = $year.', '.$month.', '.$day.', '.$hour.', '.$min;
		    $html .= '[Date.UTC('.$dateForUtc.'), '.$value.' ],';
		}

		// On supprime la dernière virgule inutile
		$html = substr($html, 0, -1);
	    
	$html .=<<<JS
	    ]
   },
JS;
	    }
	    else
	    {
		$html .=<<<JS
   {
        name: '$categorie',
        data: []
   },
JS;
	    }
	}

        if ($all === 0) {
            return array('status' => false,
                'result' => 'No Data !');
        }

        // On supprime la dernière virgule inutile
        $html = substr($html, 0, -1);
        $html .= "]});";

        return array('status' => true,
            'result' => $html);
    }
    
    /**
     * Création de colonnes à type de valeurs variable et d'une courbe par dessus
     * Syntaxe du tableau attendu :
     * array (
     *   	'categorie 1' => array(
     *     		'nb' => nbQueries,
     *     		'nameStacked' => name,
     *     		'nameLine' => name,
     *     		'dataStacked' => array(
     *       		'ip 1' => nbQueries,
     *       		'ip 2' => nbQueries,
     *       		...
     *     		),
     *     		'dataLine' => array(
     *       		'ip 1' => nbQueries,
     *       		'ip 2' => nbQueries,
     *       		...
     *     		)
     *   	),
     *   	'categorie 2 => array(
     *       	...
     *   	),
     *   	...
     *   )
     * )
     * 
     * @param array $data
     * @param string $title
     * @param string $subtitile
     * @param string $yTitle
     * @param string $container
     * @param string $unit
     * @param int $rotation
     * @param int $max
     * @param int $tickInterval
     * @return string
     */
    static public function computeStackedColumnAndBasicLine($data, $title, $subtitile, $yTitle, $container, $unit = '', $rotation = 0, $max = null, $tickInterval = null) {
    	if (empty($data)){
    		return array('status' => false,
    				'result' => 'No Data');
    	}
    
    	$tab = array_values(current($data));
    	$categories = array_keys($tab[3]);
    	$categories = implode("', '", $categories);
    	$categories = "['" . $categories . "']";
    
    	$uniqueId = time();
    
    	$maxOption = null;
    	if($max != null)
    	{
    		$maxOption = 'max: '.(int) $max.',';
    	}
    
    	$tickOption = null;
    	if($tickInterval != null)
    	{
    		$tickOption = 'tickInterval: '.(int) $tickInterval.',';
    	}
    
    	$html = <<<JS
        var chart$uniqueId;
        chart$uniqueId = new Highcharts.Chart({
        chart: {
            renderTo: '$container',
            zoomType: 'x',
            backgroundColor:'rgba(255, 255, 255, 0.1)'
        },
        title: {
            text: '$title'
        },
        subtitle: {
            text: document.ontouchstart === undefined ?
                '$subtitile' :
                '$subtitile'
        },
        xAxis: {
            categories: $categories,
            labels: {
                rotation: $rotation,
                align: 'right',
                style: {
                    fontFamily: 'Verdana, sans-serif'
                }
            }
        },
        yAxis: {
            title: {
                text: '$yTitle'
            },
            min: 0,
			$maxOption
			$tickOption
            type: 'category'
        },
        tooltip: {
            formatter: function() {
				return ''+
JS;
    	if ($unit === ''){
    		$html .= "this.series.name +': '+ this.y;";
    	} else {
    		$html .= "this.series.name +': '+ this.y +' $unit';";
    	}
    	$html .=<<<JS
					this.series.name +': '+ this.y +' ('+ Math.round(this.percentage) +'%)';
			}
        },
        legend: {
            enabled: true
        },
        plotOptions: {
			column: {
				stacking: 'normal'
			}
		},
        series: [
JS;
    
    	//Affichage des colonnes
    	$all = 0;
    	foreach ($data as $categorie => $d) {
    		$nb = $d['nb'];
    		$all += $nb;
    
    		$dataImplode = implode(',', array_map('intval', array_values($d['dataStacked'])));
    		$nom = $d['nameStacked'];
    
    		$html .=<<<JS
   {
       	name: '$nom',
       	data: [$dataImplode],
        type: 'column'
   },
JS;
    	}
    		
    	//Affichage de la courbe
    	foreach ($data as $categorie => $d) {
    		$nb = $d['nb'];
    		$all += $nb;
    	
    		$dataImplode = implode(',', array_values($d['dataLine']));
    		$nom = $d['nameLine'];
    	
    		$html .=<<<JS
   {
       	name: '$nom',
       	data: [$dataImplode],
        type: 'spline'
   },
JS;
    	}
    
    	if ($all === 0) {
    		return array('status' => false,
    				'result' => 'Aucune donnée à afficher !');
    	}
    
    	// On supprime la dernière virgule inutile
    	$html = substr($html, 0, -1);
    	$html .= "]});";
    
    	return array('status' => true,
    			'result' => $html);
    }
    
    /**
     * Création d'un histogramme avec une hiérarchie
     * Syntaxe du tableau attendu :
     * array (
     *   'categorie 1' => array(
     *     'nb' => nbQueries,
     *     'data' => array(
     *       'ip 1' => nbQueries,
     *       'ip 2' => nbQueries,
     *       ...
     *     )
     *   ),
     *   'categorie 2 => array(
     *       ...
     *   ),
     *   ...
     * )
     * @param array $data
     * @param string $title
     * @param string $subtitle
     * @param string $yTitle
     * @param string $name
     * @param string $container
     * @param string $unit
     * @param int $rotation
     * @return string
     */
    static public function computeDrilldownColumn($data, $title, $subtitle, $yTitle, $name, $container, $unit = '', $rotation = 0, $max = null, $tickInterval = null) {
    
    	if (empty($data)){
    		return array('status' => false,
    				'result' => 'No Data');
    	}
    
    	$countCat = count($data);
    	$minX = 0;
    	/*if ($countCat > 20){
    		$minX = floor($countCat/1.2);
    	}*/
    	 
    	$maxOption = null;
    	if($max != null)
    	{
    		$maxOption = 'max: '.(int) $max.',';
    	}
    	 
    	$tickOption = null;
    	if($tickInterval != null)
    	{
    		$tickOption = 'tickInterval: '.(int) $tickInterval.',';
    	}
    
    	$seriesData = "";
    	$drilldownData = "";
    	foreach($data as $categorie => $values)
    	{
    		//Série
    		$seriesData .= "
            {
                name: '".$categorie."',
                y: ".(int) $values['nb'].",
                drilldown: '". Subvitamine\Libs\LibString::camelize($categorie)."'
            },";
    
    		//DrillDown
    		$drilldownValues = "";
    		foreach($values['data'] as $subName => $val)
    		{
    			$drilldownValues .= "['".$subName."', ".(int) $val."],";
    		}
    		$drilldownValues = substr($drilldownValues, 0, -1);
    
    		$drilldownData .= "
            {
                id: '".  Subvitamine\Libs\LibString::camelize($categorie)."',
                name: '".$categorie."',
                data: [".$drilldownValues."]
            },";
    	}
    	$seriesData = substr($seriesData, 0, -1);
    	$drilldownData = substr($drilldownData, 0, -1);
    
    	$html = "
$('#".$container."').highcharts({
    chart: {
        type: 'column'
    },
    title: {
        text: '".$title."'
    },
    subtitle: {
        text: '".$subtitle."'
    },
    credits: {
        enabled: false
    },
    xAxis: {
        type: 'category',
        labels: {
            rotation: ".$rotation.",
            align: 'right',
            style: {
				fontFamily: 'Verdana, sans-serif'
			}
		},
		min: $minX
    },
    yAxis: {
    	title: {
    		text: '".$yTitle."'
    	},
    	$maxOption
    	$tickOption
    	min: 0
    },
    legend: {
    	enabled: false
    },
    plotOptions: {
    	series: {
    		borderWidth: 0,
    		dataLabels: {
    			enabled: true
    		}
    	}
    },
    exporting: {
    	enabled: true
    },
    tooltip: {
    	formatter: function() {
    		if (this.point.drilldown) {
    			var s = this.key +' : <b>'+ this.y +' $unit</b><br/>';
    			s += 'Cliquer pour voir le détail';
    		} else {
    			var s = this.key +' : <b>'+ this.y + '</b><br/>';
    		}
    		return s;
    	}
    },
    series: [{
    	name: '".$name."',
        colorByPoint: true,
        data: [".$seriesData."]
    }],
    drilldown: {
        drillUpButton: {
			relativeTo: 'spacingBox',
			position: {
				y: -5,
				x: -50
			}
		},
        series: [".$drilldownData."]
    }
});";
    
    	return array('status' => true,
    			'result' => $html);
    }
    
    /**
     * Création d'un histogramme avec une hiérarchie
     * Syntaxe du tableau attendu :
     * array (
     *   'categorie 1' => array(
     *     'nb' => nbQueries,
     *     'dataLineName' => nameLine,     
     *     'dataStackedName' => nameStacked,
     *     'dataStacked' => array(
     *       	'ip 1' => nbQueries,
     *       	'ip 2' => nbQueries,
     *       	...
     *     	),
     *     	'dataLine' => array(
     *      	'ip 1' => nbQueries,
     *      	'ip 2' => nbQueries,
     *      	...
     *     	)
     *     )
     *   ),
     *   'categorie 2 => array(
     *       ...
     *   ),
     *   ...
     * )
     * @param array $data
     * @param string $title
     * @param string $subtitle
     * @param string $yTitle
     * @param string $name
     * @param string $container
     * @param string $unit
     * @param int $rotation
     * @return string
     */
    static public function computeDrilldownColumnAndBasicLine($data, $title, $subtitle, $yTitle, $name, $container, $unit = '', $rotation = 0, $max = null, $tickInterval = null) {
    
    	if (empty($data)){
    		return array('status' => false,
    				'result' => 'No Data');
    	}
    
    	$countCat = count($data);
    	$minX = 0;
    	/*if ($countCat > 20){
    		$minX = floor($countCat/1.2);
    	}*/
    
    	$maxOption = null;
    	if($max != null)
    	{
    		$maxOption = 'max: '.(int) $max.',';
    	}
    
    	$tickOption = null;
    	if($tickInterval != null)
    	{
    		$tickOption = 'tickInterval: '.(int) $tickInterval.',';
    	}
    	
    	$colors = array(
    			'#7cb5ec',
    			'#434348', 
    			'#90ed7d', 
    			'#f7a35c', 
    			'#8085e9',
    			'#f15c80', 
    			'#e4d354', 
    			'#2b908f', 
    			'#f45b5b',     			
    			'#91e8e1'
    	);
    
    	$seriesData = "";
    	$drilldownStacked = "";
    	$drilldownLine = "";
    	$i = 1;
    	foreach($data as $categorie => $values)
    	{
    		//Série
    		$seriesData .= "
            {
                name: '".$categorie."',
                y: ".(int) $values['nb'].",
                drilldown: '".  Subvitamine\Libs\LibString::camelize($categorie)."'
            },";
    
    		//DrillDown Stacked
    		$drilldownStackedValues = "";
    		foreach($values['dataStacked'] as $subName => $val)
    		{
    			$drilldownStackedValues .= "['".$subName."', ".(int) $val."],";
    		}
    		$drilldownStackedValues = substr($drilldownStackedValues, 0, -1);
    
    		$drilldownStacked .= "
            {
                id: '".  Subvitamine\Libs\LibString::camelize($categorie)."',
                name: '".$values['dataStackedName']."',
                type: 'column',
                data: [".$drilldownStackedValues."]
            },";
    
    		//DrillDown Line
    		$drilldownLineValues = "";
    		foreach($values['dataLine'] as $subName => $val)
    		{
    			$drilldownLineValues .= "['".$subName."', ".(int) $val."],";
    		}
    		$drilldownLineValues = substr($drilldownLineValues, 0, -1);
    
    		if(!isset($colors[$i]))
    		{
    			$i = 1;
    		}
    		$drilldownLine .= "
            {
                id: '".  Subvitamine\Libs\LibString::camelize($categorie)."',
                name: '".$values['dataLineName']."',
                type: 'spline',
                color: '".$colors[$i]."',
                data: [".$drilldownLineValues."]
            },";
    		$i++;
    	}
    	$seriesData = substr($seriesData, 0, -1);
    	$drilldownStacked = substr($drilldownStacked, 0, -1);
    	$drilldownLine = substr($drilldownLine, 0, -1);
    
    	$html = "
$('#".$container."').highcharts({
    chart: {
        type: 'column',
		events: {
			drilldown: function(e) {
            	var chart = this;
				drilldowns = chart.userOptions.drilldown.series;
				series = [];
            	e.preventDefault();
				Highcharts.each(drilldowns, function(p, i) {
					if (p.id.includes(e.point.drilldown)) {
						chart.addSingleSeriesAsDrilldown(e.point, p);
					}
				});
				chart.applyDrilldown();
			}
        }
    },
    title: {
        text: '".$title."'
    },
    subtitle: {
        text: '".$subtitle."'
    },
    credits: {
        enabled: false
    },
    xAxis: {
        type: 'category',
        labels: {
            rotation: ".$rotation.",
            align: 'right',
            style: {
            	fontFamily: 'Verdana, sans-serif'
    		}
    	},
    	min: $minX
    },
    yAxis: {
    	title: {
    		text: '".$yTitle."'
    	},
    	$maxOption
    	$tickOption
    	min: 0
    },
    legend: {
    	enabled: true
    },
    plotOptions: {
    	series: {
    		borderWidth: 0,
    		dataLabels: {
    			enabled: true
    		}
    	}
    },
    exporting: {
    	enabled: true
    },
    tooltip: {
    	formatter: function() {
    		if (this.point.drilldown) {
    			var s = this.key +' : <b>'+ this.y +' $unit</b><br/>';
    			s += 'Cliquer pour voir le détail';
    		} else {
    			var s = this.key +' : <b>'+ this.y + '</b><br/>';
    		}
    		return s;
    	}
    },
    series: [{
    	name: '".$name."',
        colorByPoint: true,
        data: [".$seriesData."]
    }],
    drilldown: {
        drillUpButton: {
			relativeTo: 'spacingBox',
			position: {
				y: -5,
				x: -50
			}
		},
        series: [".$drilldownStacked.",".$drilldownLine."]
    }
});";
    
    	return array('status' => true,
    			'result' => $html);
    }
    
    /**
     * Création d'une toile d'arraignée
     * Syntaxe du tableau attendu : 
     * array (
     *   'serie 1' => array(
     *     'data' => array(
     *       'categorie 1' => nbQueries,
     *       'categorie 2' => nbQueries,
     *       ...
     *     )      
     *   ),
     *   'serie 2 => array(
     *       ...
     *   ),
     *   ...
     * )
     * @param array $data
     * @param string $title
     * @param string $subtitle
     * @param string $container
     * @param string $unit
     * @param int $max
     * @return string
     */
    static public function computeSpiderChart($data, $title, $subtitle, $container, $unit = '', $max = null, $tickInterval = null, $legend = 1) {
    	if (empty($data)){
    		return array('status' => false,
    				'result' => 'No Data');
    	}
    	
    	$maxOption = null;
    	if($max != null)
    	{
    		$maxOption = 'max: '.(int) $max.',';
    	}
    
    	$tickOption = null;
    	if($tickInterval != null)
    	{
    		$tickOption = 'tickInterval: '.(int) $tickInterval.',';
    	}

    	//Construction de la série
    	$series = '';
    	foreach ($data as $categorie => $d) {    	
    		$subcategories_value = implode(',', array_map('intval', array_values($d['data'])));
    		$categories = implode(',', array_map('Subvitamine\Libs\LibString::escape', array_keys($d['data'])));
    	
    		$series .="{
                        pointPlacement: 'on',
						name: '$categorie',
						data: [$subcategories_value]
					},";
    	}
    	
    	// On supprime la dernière virgule inutile
    	$series = substr($series, 0, -1);
    	
    	$uniqid = uniqid();
    	
    	$html = "
var categories".$uniqid." = [".$categories."];
		
$('#".$container."').highcharts({    	
	chart: {
		polar: true,
		type: 'line'
	},
	title: {
		text: '".$title."',
		x: -80
	},
    subtitle: {
        text: '".$subtitle."'
    },
    credits: {
        enabled: false
    },
	pane: {
		size: '80%'
	},
	xAxis: {
		categories: categories".$uniqid.",
		tickmarkPlacement: 'on',
		lineWidth: 0,
		labels: {
			formatter: function () {
				var tempId = categories".$uniqid.".indexOf(this.value) + 1;
				return '<b>' + tempId + '</b>';
			}
		}
	},
	yAxis: {
		gridLineInterpolation: 'polygon',
		lineWidth: 0,
		$maxOption
		$tickOption
		min: 0
	},
	tooltip: {
		shared: true,
		pointFormat: '<span style=\"color:{series.color}\">{series.name}: <b>\{point.y:,.0f} ".$unit."</b><br/>'
	},
	legend: {
		enabled: ".$legend.",
		align: 'right',
		verticalAlign: 'top',
		y: 70,
		layout: 'vertical'
	},				
	series: [".$series."]    	
});";
    	
    	return array('status' => true,
    			'result' => $html);
    }
}