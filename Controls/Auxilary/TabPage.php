<?php
/**
 * TabPage class
 *
 * A TabPage represents a Tab and Panel in the TabPanel Control.
 * 
 * <pre>
 *     $tabPanel = new TabPanel();
 *     $cars = new TabPage('Cars');
 *     $trains = new TabPage('Trains');
 *     
 *     $tabPanel->TabPages->AddRange($cars, $trains);
 * </pre>
 * @package Controls/Auxiliary
 */
class TabPage extends Panel 
{
	private $RolloverTab;
	
	function TabPage($tabName='TabPage')
	{
		parent::Panel(0, 0, '100%', '100%');
		$this->SetRolloverTab($tabName);
	}
	/*
	 * Assigns the RolloverTab to be used with the TabPage. 
	 * This is useful in situations where you prefer to set a custom look and feel
	 * for the RolloverTab.
	 */
	public function SetRolloverTab($rolloverTab = null)
	{
		if(is_string($rolloverTab))
			$rolloverTab = new RolloverTab($rolloverTab);
		$this->RolloverTab = $rolloverTab;
	}
	/**
	 * Returns the RolloverTab associated with the TabPage
	 * @return RolloverTab
	 */
	public function GetRolloverTab(){return $this->RolloverTab;}
	public function SetText($text)	{$this->RolloverTab->SetText($text);}
	public function GetText()		{return $this->RolloverTab->GetText();}
}
?>