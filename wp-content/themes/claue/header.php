<?php

/**
 * The header for our theme.
 *
 * @since   1.0.0
 * @package Claue
 */

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php bloginfo('charset'); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<meta name="keywords" content="Commercial Refrigeration, Reach-in Cabinets, Undercounter Refrigeration, Blast Freezers, Ice Cubers, Flake Ice Machines, Wine Coolers, Wine Walls, Back Bars, Bottle Coolers, Draught Beer Systems, Soft Serve Freezers, Granita Dispensers, Juice Dispensers, Hot Chocolate Dispensers, Confectionery Display Cases, Chest Freezers & Coolers, Minibars, Multideck Chillers, Deli Counters, Visi Coolers, Visi Freezers, Walk-in Coldrooms, Cold Storages, Pre-Coolers, Ultra Low Freezers, Pharmacy Freezers & Coolers, Blood Bank Refrigeration, Mobile Coolers, Fully Automatic Coffee Machines, Traditional Coffee Machines, Coffee Bean Grinders, Commercial Blenders, Cold Pressed Juicers, Combi Steamers, Convection Ovens, Commercial Microwave Ovens, Cooking & Catering Products, Fryers, Food Prep Equipment, Speed Ovens, Induction Cooking Systems, Rotary Rack Ovens, Deck Ovens, Bakery Mixers, Dough Sheeters & Dividers, Bread Slicers" />
	<meta name="description" content="Trufrost is a commercial refrigeration and foodservice equipment company with a fresh, contemporary view. We bring a truly international range of commercial cooling and foodservice products & solutions for hotels, restaurants, bars & pubs, coffee shops, ice cream & beverage, food retail & display, food preservation and the bio-medical & healthcare segments." />
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>">
	<link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@300&display=swap" rel="stylesheet">
	<?php wp_head(); ?>
	<!-- Global site tag (gtag.js) - Google Analytics -->
	<script async src="https://www.googletagmanager.com/gtag/js?id=UA-178511942-1"></script>
	<script>
		window.dataLayer = window.dataLayer || [];

		function gtag() {
			dataLayer.push(arguments);
		}
		gtag('js', new Date());

		gtag('config', 'UA-178511942-1');
	</script>

	<script src="https://www.googleoptimize.com/optimize.js?id=OPT-MFWF6GR"></script>

	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Ubuntu:ital,wght@0,300;0,400;0,500;0,700;1,300;1,400;1,500;1,700&display=swap" rel="stylesheet">

	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

	<!-- Global site tag (gtag.js) - Google Analytics -->
	<script async src="https://www.googletagmanager.com/gtag/js?id=G-2J1KTEEVHF"></script>
	<script>
		window.dataLayer = window.dataLayer || [];

		function gtag() {
			dataLayer.push(arguments);
		}
		gtag('js', new Date());

		gtag('config', 'G-2J1KTEEVHF');
	</script>

</head>

<body <?php body_class(); ?>


	<?php jas_claue_schema_metadata(array('context' => 'body')); ?>>

	<div id="jas-wrapper">
		<?php jas_claue_header(); ?>