<?php
/**
 * Animate class
 *
 * This class contains various static functions and constants pertaining to the animation of specified Controls.
 * If there exist other Controls that Shift With the specified Control, they too will be animated. See the Shift class for more information.
 * If the AnimationStart and AnimationStop Events for that Control have been defined, they will be launched when
 * the animation starts and stops, respectfully.
 * 
 * <pre>
 * // Animate's a Panel's Location to 100px Left and 200px Top.
 * Animate::Location($panel, 100, 200);
 * // Fades a Panel and then removes it.
 * Animate::Opacity($panel, Animate::Oblivion);
 * </pre>
 * 
 * @package Statics
 */
final class Animate
{
	/**
	 * A possible value for the easing parameter, Linear indicates that position will be proportional to time, i.e., no easing.
	 */
	const Linear = 1;
	/**
	 * A possible and default value for the easing parameter, Quadratic indicates that position will be a second degree polynomial of time, i.e., standard easing.
	 */
	const Quadratic = 2;
	/**
	 * A possible value for the easing parameter, Cubic indicates that position will be a third degree polynomial of time, i.e., sharper easing than Quadratic.
	 */
	const Cubic = 3;
	/**
	 * A possible value for the to parameter, Oblivion indicates that the Control's property will go to 1 and subsequently be removed from its Parent's ArrayList.
	 */
	const Oblivion = '"Oblivion"';
	/**
	 * A possible value for the to parameter, Hiding indicates that the Control's property will go to 1 and subsequently becomes invisible.
	 */
	const Hiding = '"Hiding"';
	
	private function Animate() {}
	/**
	 * Animates a specified Control's specified property (in the sense of JavaScript e.g., "style.borderWidth") to a specified position over time.
	 * @param Control $control The Control to be animated
	 * @param string $property The property to be animated
	 * @param mixed $to The destination to which the property will go. Can be an integer, Animate::Oblivion or Animate::Hiding.
	 * @param integer $duration The number of milliseconds it will take for the animation to complete
	 * @param string $units The units that are to be used for this property
	 * @param mixed $easing The type of easing motion associated with this animation. See the Animate constants for more details.
	 * @param integer $from If you would like the property to begin somewhere other than its current value
	 * @param integer $fps The frame rate of this animation
	 */
	static function Property($control, $property, $to, $duration=1000, $units='px', $easing=Animate::Quadratic, $from=null, $fps=30, $numArgs=null)
	{
		AddNolohScriptSrc('Animation.js', true);
		$args = func_get_args();
		if($numArgs)
			$args = array_slice($args, 0, $numArgs);
		else 
			$numArgs = func_num_args();
		$args[0] = '\'' . $control->Id . '\'';
		$args[1] = '\'' . $property . '\'';
		if($numArgs > 4)
		{
			$args[4] = '\'' . $units . '\'';
			if($numArgs > 6)
				if($from===null)
					$args[6] = 'null';
				elseif($property === 'scrollLeft' || $property === 'scrollTop')
					QueueClientFunction($control, '_NChange', array('"'.$control->Id.'"', '"'.$property.'"', $from), false);
				else
					NolohInternal::SetProperty($property, $from.$units, $control);
		}
		QueueClientFunction($control, 'new _NAni', $args, false, Priority::Low);
	}
	/**
	 * Animates a specified Control's location horizontally
	 * @param Control $control The Control to be animated
	 * @param mixed $to The destination to which the Left will go. Can be an integer, another Control, Animate::Oblivion or Animate::Hiding.
	 * @param integer $duration The number of milliseconds it will take for the animation to complete
	 * @param mixed $easing The type of easing motion associated with this animation. See the Animate constants for more details.
	 * @param integer $from If you would like the Left to begin somewhere other than its current value
	 * @param integer $fps The frame rate of this animation
	 */
	static function Left($control, $to, $duration=1000, $easing=Animate::Quadratic, $from=null, $fps=30)
	{
		if($to instanceof Control)
			$to = '_N(\''. $to->Id .'\').offsetLeft';
		$numArgs = func_num_args();
		Animate::Property($control, 'style.left', $to, $duration, 'px', $easing, $from, $fps, $numArgs+($numArgs>=4?2:1));
	}
	/**
	 * Animates a specified Control's location vertically
	 * @param Control $control The Control to be animated
	 * @param mixed $to The destination to which the Top will go. Can be an integer, another Control, Animate::Oblivion or Animate::Hiding.
	 * @param integer $duration The number of milliseconds it will take for the animation to complete
	 * @param mixed $easing The type of easing motion associated with this animation. See the Animate constants for more details.
	 * @param integer $from If you would like the Top to begin somewhere other than its current value
	 * @param integer $fps The frame rate of this animation
	 */
	static function Top($control, $to, $duration=1000, $easing=Animate::Quadratic, $from=null, $fps=30)
	{
		if($to instanceof Control)
			$to = '_N(\''. $to->Id .'\').offsetTop';
		$numArgs = func_num_args();
		Animate::Property($control, 'style.top', $to, $duration, 'px', $easing, $from, $fps, $numArgs+($numArgs>=4?2:1));
	}
	/**
	 * Animates a specified Control's location both horizontally and vertically
	 * @param Control $control The Control to be animated
	 * @param mixed $toLeft The horizontal destination to which the Left will go. Can be an integer, another Control, Animate::Oblivion or Animate::Hiding.
	 * @param mixed $toTop The vertical destination to which the Top will go. Can be an integer, another Control, Animate::Oblivion or Animate::Hiding.
	 * @param integer $duration The number of milliseconds it will take for the animation to complete
	 * @param mixed $easing The type of easing motion associated with this animation. See the Animate constants for more details.
	 * @param integer $fromLeft If you would like the Left to begin somewhere other than its current value
	 * @param integer $fromTop If you would like the Top to begin somewhere other than its current value
	 * @param integer $fps The frame rate of this animation
	 */
	static function Location($control, $toLeft, $toTop, $duration=1000, $easing=Animate::Quadratic, $fromLeft=null, $fromTop=null, $fps=30)
	{
		if($toLeft instanceof Control)
			$toLeft = '_N(\''. $to->Id .'\').offsetLeft';
		if($toTop instanceof Control)
			$toTop = '_N(\''. $to->Id .'\').offsetLeft';
		$numArgs = func_num_args();
		if($numArgs >= 5)
			++$numArgs;
		Animate::Property($control, 'style.left', $toLeft, $duration, 'px', $easing, $fromLeft, $fps, $numArgs);
		Animate::Property($control, 'style.top', $toTop, $duration, 'px', $easing, $fromTop, $fps, $numArgs);
	}
	/**
	 * Animates a specified Control's size horizontally
	 * @param Control $control The Control to be animated
	 * @param mixed $to The destination to which the Width will go. Can be an integer, Animate::Oblivion or Animate::Hiding.
	 * @param integer $duration The number of milliseconds it will take for the animation to complete
	 * @param mixed $easing The type of easing motion associated with this animation. See the Animate constants for more details.
	 * @param integer $from If you would like the Width to begin somewhere other than its current value
	 * @param integer $fps The frame rate of this animation
	 */
	static function Width($control, $to, $duration=1000, $easing=Animate::Quadratic, $from=null, $fps=30)
	{
		$numArgs = func_num_args();
		Animate::Property($control, 'style.width', $to, $duration, 'px', $easing, $from, $fps, $numArgs+($numArgs>=4?2:1));
	}
	/**
	 * Animates a specified Control's size vertically
	 * @param Control $control The Control to be animated
	 * @param mixed $to The destination to which the Height will go. Can be an integer, Animate::Oblivion or Animate::Hiding.
	 * @param integer $duration The number of milliseconds it will take for the animation to complete
	 * @param mixed $easing The type of easing motion associated with this animation. See the Animate constants for more details.
	 * @param integer $from If you would like the Height to begin somewhere other than its current value
	 * @param integer $fps The frame rate of this animation
	 */
	static function Height($control, $to, $duration=1000, $easing=Animate::Quadratic, $from=null, $fps=30)
	{
		$numArgs = func_num_args();
		Animate::Property($control, 'style.height', $to, $duration, 'px', $easing, $from, $fps, $numArgs+($numArgs>=4?2:1));
	}
	/**
	 * Animates a specified Control's size both horizontally and vertically
	 * @param Control $control The Control to be animated
	 * @param mixed $toWidth The horizontal destination to which the Width will go. Can be an integer, Animate::Oblivion or Animate::Hiding.
	 * @param mixed $toHeight The vertical destination to which the Height will go. Can be an integer, Animate::Oblivion or Animate::Hiding.
	 * @param integer $duration The number of milliseconds it will take for the animation to complete
	 * @param mixed $easing The type of easing motion associated with this animation. See the Animate constants for more details.
	 * @param integer $fromWidth If you would like the Left to begin somewhere other than its current value
	 * @param integer $fromHeight If you would like the Top to begin somewhere other than its current value
	 * @param integer $fps The frame rate of this animation
	 */
	static function Size($control, $toWidth, $toHeight, $duration=1000, $easing=Animate::Quadratic, $fromWidth=null, $fromHeight=null, $fps=30)
	{
		$numArgs = func_num_args();
		if($numArgs >= 5)
			++$numArgs;
		Animate::Property($control, 'style.width', $toWidth, $duration, 'px', $easing, $fromWidth, $fps, $numArgs);
		Animate::Property($control, 'style.height', $toHeight, $duration, 'px', $easing, $fromHeight, $fps, $numArgs);
	}
	/**
	 * Animates a specified Control's scrollbar horizontally
	 * @param Control $control The Control to be animated
	 * @param mixed $to The destination to which the ScrollLeft will go. Can be an integer, another Control, Layout::Left, Layout::Right, Animate::Oblivion or Animate::Hiding.
	 * @param integer $duration The number of milliseconds it will take for the animation to complete
	 * @param mixed $easing The type of easing motion associated with this animation. See the Animate constants for more details.
	 * @param mixed $from If you would like the ScrollLeft to begin somewhere other than its current value. Can be an integer or Layout::Left or Layout::Right.
	 * @param integer $fps The frame rate of this animation
	 */
	static function ScrollLeft($control, $to, $duration=1000, $easing=Animate::Quadratic, $from=null, $fps=30)
	{
		if($to instanceof Control)
//			$to = '_N(\''. $to->Id .'\').offsetLeft';
			$to = '_NFindX(\''. $to->Id .'\',\''. $control->Id .'\')';
		elseif($to === Layout::Left)
			$to = 0;
		elseif($to === Layout::Right)
			$to = '_N(\''.$control->Id.'\').scrollWidth';
		if($from === Layout::Left)
			$from = 0;
		elseif($from === Layout::Right)
			$from = '_N(\''.$control->Id.'\').scrollWidth';
		$numArgs = func_num_args() + 2;
		Animate::Property($control, 'scrollLeft', $to, $duration, '', $easing, $from, $fps, max(5, $numArgs));
	}
	/**
	 * Animates a specified Control's scrollbar vertically
	 * @param Control $control The Control to be animated
	 * @param mixed $to The destination to which the ScrollTop will go. Can be an integer, another Control, Layout::Top, Layout::Bottom, Animate::Oblivion or Animate::Hiding.
	 * @param integer $duration The number of milliseconds it will take for the animation to complete
	 * @param mixed $easing The type of easing motion associated with this animation. See the Animate constants for more details.
	 * @param mixed $from If you would like the ScrollTop to begin somewhere other than its current value. Can be an integer or Layout::Top or Layout::Bottom.
	 * @param integer $fps The frame rate of this animation
	 */
	static function ScrollTop($control, $to, $duration=1000, $easing=Animate::Quadratic, $from=null, $fps=30)
	{
		if($to instanceof Control)
//			$to = '_N(\''. $to->Id .'\').offsetTop';
			$to = '_NFindY(\''. $to->Id .'\',\''. $control->Id .'\')';
		elseif($to === Layout::Top)
			$to = 0;
		elseif($to === Layout::Bottom)
			$to = '_N(\''.$control->Id.'\').scrollHeight';
		if($from === Layout::Top)
			$from = 0;
		elseif($from === Layout::Right)
			$from = '_N(\''.$control->Id.'\').scrollHeight';
		$numArgs = func_num_args() + 2;
		Animate::Property($control, 'scrollTop', $to, $duration, '', $easing, $from, $fps, max(5, $numArgs));
	}
	/**
	 * Animates a specified Control's opacity
	 * @param Control $control The Control to be animated
	 * @param mixed $to The destination to which the Opacity will go. Can be an integer, Animate::Oblivion or Animate::Hiding.
	 * @param integer $duration The number of milliseconds it will take for the animation to complete
	 * @param mixed $easing The type of easing motion associated with this animation. See the Animate constants for more details.
	 * @param mixed $from If you would like the Opacity to begin somewhere other than its current value
	 * @param integer $fps The frame rate of this animation
	 */
	static function Opacity($control, $to, $duration=1000, $easing=Animate::Quadratic, $from=null, $fps=30)
	{
		$numArgs = func_num_args() + 2;
		Animate::Property($control, 'opacity', $to, $duration, '', $easing, $from, $fps, max(5, $numArgs));
	}
}

?>