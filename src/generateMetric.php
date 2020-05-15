<?php  
# loading the thread sizes and pitches
$threads = json_decode(file_get_contents('threads.json'),true);

# the thread angles    overhang angle = 90 - angle/2
$angles = [90,80,70,60,50]; 

# the maximum +- tolerance
$tolMax = 0.5;

# The tolerance step size 
$tolStep = 0.025;

foreach ($angles as $angle) {
	$filename = '../FDM' . $angle . 'MetricTrapezoidalThreads.xml';
	$name = 'FDM ' . $angle . ' Degree Metric Trapezoidal Threads';

	$threadType = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><ThreadType></ThreadType>');
	$threadType->addChild('Name', $name); 
	$threadType->addChild('CustomName', $name); 
	$threadType->addChild('Unit', 'mm'); 
	$threadType->addChild('Angle', $angle);
	$threadType->addChild('SortOrder', 4); 

	foreach ($threads as $size => $pitches) {
		$threadSize = $threadType->addChild('ThreadSize'); 
		$threadSize->addChild('Size', $size);

		foreach ($pitches as $pitch) {
			$designation = $threadSize->addChild('Designation');
			$designation->addChild('ThreadDesignation', 'FDM' . $angle . '-' . $size . 'x' . $pitch);
			$designation->addChild('CTD', 'FDM' . $angle . '-' . $size . 'x' . $pitch);
			$designation->addChild('Pitch', $pitch);

			/*
			 * $pitch = thread pitch
			 * $angle = thread form angle
			 * $size = nominal diameter of the thread
			 * $radius = nominal radius of the thread
			 * $height = nominal height of the thread form
			 * $crestH = distance between the crest and the top of the v thread form
			 * $pitchH = distance between the pitch radius and the top of the v thread form
			 * $rootH = distance between the root and the bottom of the v thread form
			 * $MajorRadius = the major radius of the nominal thread
			 * $pitchRadius = the pitch radius of the nominal thread
			 * $minorRadius = the minor radius of the nominal thread
			 */
			$radius = $size/2;
			$height = tan(deg2rad(90-($angle/2)))*($pitch/2);

			$crestH = tan(deg2rad(90-($angle/2)))*($pitch/8);
			$pitchH = tan(deg2rad(90-($angle/2)))*($pitch/4);
			$rootH = tan(deg2rad(90-($angle/2)))*($pitch/8);

			$MajorRadius = $radius;
			$pitchRadius = $radius-$pitchH+$crestH;
			$minorRadius = $radius-$height+$rootH+$crestH;

			$tol = 0;
			while ( $tol <= $tolMax && $tol <= $crestH && $tol <= $rootH) {
				/*
				 * $tol = the tolerance between the actual thread and the nominal thread
				 * $externalMajorD = the major diameter of the external thread
				 * $externalPitchD = the pitch diameter of the external thread
				 * $externalMinorD = the minor diameter of the external thread
				 * $internalMajorD = the major diameter of the internal thread
				 * $internalPitchD = the pitch diameter of the internal thread
				 * $internalMinorD = the minor diameter of the internal thread
				 */
				$externalMajorD = ($MajorRadius-$tol)*2;
				$externalPitchD = ($pitchRadius-($tol/sin(deg2rad($angle/2))))*2;
				$externalMinorD = ($minorRadius-$tol)*2;
				$internalMajorD = ($MajorRadius+$tol)*2;
				$internalPitchD = ($pitchRadius+($tol/sin(deg2rad($angle/2))))*2;
				$internalMinorD = ($minorRadius+$tol)*2;

				$thread = $designation->addChild('Thread');
				$thread->addChild('Gender', 'external');
				$thread->addChild('Class', $tol . 'e');
				$thread->addChild('MajorDia', $externalMajorD);
				$thread->addChild('PitchDia', $externalPitchD);
				$thread->addChild('MinorDia', $externalMinorD);

				$thread = $designation->addChild('Thread');
				$thread->addChild('Gender', 'internal');
				$thread->addChild('Class', $tol . 'i');
				$thread->addChild('MajorDia', $internalMajorD);
				$thread->addChild('PitchDia', $internalPitchD);
				$thread->addChild('MinorDia', $internalMinorD);

				$tol += $tolStep;
			}
		}
	}

	$dom = new DOMDocument('1.0');
	$dom->preserveWhiteSpace = false;
	$dom->formatOutput = true;
	$dom->loadXML($threadType->asXML());
	$dom->save($filename);
}
?>