<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * diceforge implementation : © Thibaut Brissard <docthib@hotmail.com> & Vincent Toper <vincent.toper@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 * 
 * diceforge.action.php
 *
 * diceforge main action entry point
 *
 *
 * In this file, you are describing all the methods that can be called from your
 * user interface logic (javascript).
 *       
 * If you define a method "myAction" here, then you can call it from your javascript code with:
 * this.ajaxcall( "/diceforge/diceforge/myAction.html", ...)
 *
 */
  
  
class action_diceforge extends APP_GameAction
{ 
    // Constructor: please do not modify
   	public function __default()
  	{
  	    if( self::isArg( 'notifwindow') )
  	    {
            $this->view = "common_notifwindow";
  	        $this->viewArgs['table'] = self::getArg( "table", AT_posint, true );
  	    }
  	    else
  	    {
            $this->view = "diceforge_diceforge";
            self::trace( "Complete reinitialization of board game" );
      	}
  	} 
  	
  	// TODO: defines your action entry points there

    public function actRollDice()
    {
        self::setAjaxMode();
        $this->game->actRollDice();
        self::ajaxResponse();
    }
	
	public function actRessourceChoice() 
	{
		self::setAjaxMode();
	
		$side           = self::getArg( "side", AT_alphanum, true );
		$side_gold      = self::getArg( "side-gold", AT_int, false );
		$side_hammer    = self::getArg( "side-hammer", AT_int, false );
		$side_vp        = self::getArg( "side-vp", AT_int, false );
		$side_moonshard = self::getArg( "side-moonshard", AT_int, false );
		$side_fireshard = self::getArg( "side-fireshard", AT_int, false );
		$side_loyalty   = self::getArg( "side-loyalty", AT_int, false );
		$side_ancientshard   = self::getArg( "side-ancientshard", AT_int, false );
		$side_maze   = self::getArg( "side-maze", AT_int, false );
		$sideNum        = self::getArg( "sideNum", AT_int, true );
		
		$ressources = array(
				'gold'      => $side_gold,
				'vp'        => $side_vp,
				'hammer'    => $side_hammer,
				'moonshard' => $side_moonshard,
				'fireshard' => $side_fireshard,
				'ancientshard' => $side_ancientshard,
				'loyalty'	=> $side_loyalty,
				'maze'		=> $side_maze
		);
		
		$this->game->actTakeRessource($sideNum, $side, $ressources);
		
		self::ajaxResponse( );
	}
	
	public function actOustedRessources() 
	{
		self::setAjaxMode();
		
		$side           = self::getArg( "side", AT_alphanum, true );
		$side_gold      = self::getArg( "side-gold", AT_int, false );
		$side_hammer    = self::getArg( "side-hammer", AT_int, false );
		$side_vp        = self::getArg( "side-vp", AT_int, false );
		$side_moonshard = self::getArg( "side-moonshard", AT_int, false );
		$side_fireshard = self::getArg( "side-fireshard", AT_int, false );
		$side_loyalty   = self::getArg( "side-loyalty", AT_int, false );
		$side_ancientshard   = self::getArg( "side-ancientshard", AT_int, false );
		$side_maze   = self::getArg( "side-maze", AT_int, false );
		$sideNum        = self::getArg( "sideNum", AT_int, true );
		
		$ressources = array(
				'gold'      => $side_gold,
				'vp'        => $side_vp,
				'hammer'    => $side_hammer,
				'moonshard' => $side_moonshard,
				'fireshard' => $side_fireshard,
				'ancientshard' => $side_ancientshard,
				'loyalty'	=> $side_loyalty,
				'maze'		=> $side_maze
		);
		
		$this->game->actOustedRessources($sideNum, $side, $ressources);
		self::ajaxResponse( );
	}
	
	public function actAutoHammer() {
		self::setAjaxMode();
		$enable      = self::getArg( "enable", AT_bool, false );
		if ($enable == true)
			$todo = 'enable';
		else
			$todo = 'disable';
		$this->game->ActAutoHammer($todo); 
		self::ajaxResponse( );
	}
	
	public function actExploitRessource() 
	{
		self::setAjaxMode();
		
		$side           = self::getArg( "side", AT_alphanum, true );
		$side_gold      = self::getArg( "side-gold", AT_int, false );
		$side_hammer    = self::getArg( "side-hammer", AT_int, false );
		$side_vp        = self::getArg( "side-vp", AT_int, false );
		$side_moonshard = self::getArg( "side-moonshard", AT_int, false );
		$side_fireshard = self::getArg( "side-fireshard", AT_int, false );
		$side_loyalty   = self::getArg( "side-loyalty", AT_int, false );
		$side_ancientshard   = self::getArg( "side-ancientshard", AT_int, false );
		$side_maze   = self::getArg( "side-maze", AT_int, false );
		$sideNum        = self::getArg( "sideNum", AT_int, true );
		
		$ressources = array(
				'gold'      => $side_gold,
				'vp'        => $side_vp,
				'hammer'    => $side_hammer,
				'moonshard' => $side_moonshard,
				'fireshard' => $side_fireshard,
				'ancientshard' => $side_ancientshard,
				'loyalty'	=> $side_loyalty,
				'maze'		=> $side_maze
		);
		
		$this->game->actExploitRessource($sideNum, $side, $ressources);

		self::ajaxResponse( );
	}
	
		public function actUseTritonToken() 
	{
		self::setAjaxMode();
		
		$side_gold      = self::getArg( "side-gold", AT_int, false );
		$side_hammer    = self::getArg( "side-hammer", AT_int, false );
		$side_vp        = self::getArg( "side-vp", AT_int, false );
		$side_moonshard = self::getArg( "side-moonshard", AT_int, false );
		$side_fireshard = self::getArg( "side-fireshard", AT_int, false );
		
		$ressources = array(
				'gold'      => $side_gold,
				'vp'        => $side_vp,
				'hammer'    => $side_hammer,
				'moonshard' => $side_moonshard,
				'fireshard' => $side_fireshard
		);
		
		$this->game->actUseTritonToken($ressources);

		self::ajaxResponse( );
	}
	
	public function actReinforcement()
	{
		self::setAjaxMode();
		$card_id  = self::getArg( "card_id", AT_int, true );
		$dice_num = self::getArg( "dice_num", AT_int, false );
		$owl      = self::getArg( "owl", AT_alphanum, false );
		
		$merchant_nbupgrade = self::getArg( "merchant_nbupgrade", AT_int, false );
		$merchant_old_side   = self::getArg( "sideToReplace", AT_int, false );
		$merchant_new_side = self::getArg( "sideToForge", AT_int, false );
		$merchant = array ('nbUpgrade' => $merchant_nbupgrade, 'old_side' => $merchant_old_side, 'new_side' => $merchant_new_side);
		
		$this->game->actReinforcement($card_id, $owl, $dice_num, $merchant);
		self::ajaxResponse( );
	}
	
	public function actAncestorSelect()
	{
		self::setAjaxMode();
		$dice_num = self::getArg( "dice_num", AT_int, true );
		
		$this->game->actAncestorSelect($dice_num);
		self::ajaxResponse( );
	}
	
	public function actCelestialUpgrade() {
		self::setAjaxMode();
		
		$new_side   = self::getArg( "sideToForge", AT_int, false );
		$old_side = self::getArg( "sideToReplace", AT_int, false );
		
		$this->game->actCelestialUpgrade($old_side, $new_side);
		
		self::ajaxResponse( );
	}
	
	public function actCancelCelestial() {
		self::setAjaxMode();
		
		$this->game->actCancelCelestial();
		
		self::ajaxResponse( );
	}
	
	public function actChooseMazePath() {
		self::setAjaxMode();
		
		$new_position   = self::getArg( "newPosition", AT_int, true );
		
		$this->game->actChooseMazePath($new_position);
		
		self::ajaxResponse( );
	}
	
	public function actChooseTreasure() {
		self::setAjaxMode();
		
		$treasure   = self::getArg( "treasure", AT_alphanum, true );
		
		$this->game->actChooseTreasure($treasure);
		
		self::ajaxResponse( );
	}
	
	public function actPuzzleMaze() {
		self::setAjaxMode();
		
		$this->game->actPuzzleMaze();
		
		self::ajaxResponse( );
	}
	
	public function actMazePowerConfirm() {
		self::setAjaxMode();
		$use = self::getArg("use", AT_bool, true);
		$this->game->actMazePowerConfirm($use);
		
		self::ajaxResponse( );
	}
	
	public function actPuzzleCelestial() {
		self::setAjaxMode();
		
		$this->game->actPuzzleCelestial();
		
		self::ajaxResponse( );
	}
	
	public function actDraft()
	{
		self::setAjaxMode();
		$exploit  = self::getArg( "card_type", AT_alphanum, true );
		
		$this->game->actDraft($exploit);
		self::ajaxResponse( );
	}
	
	public function actSideChoice()
	{
		self::setAjaxMode();
		
		$side1 = self::getArg("side1", AT_alphanum, false);
		$side2 = self::getArg("side2", AT_alphanum, false);
		$side98 = self::getArg("side98", AT_alphanum, false);
		
		$this->game->actSideChoice($side1, $side2, $side98);
		
		self::ajaxResponse( );
	}
	
	public function actUseCerberusToken()
	{
		self::setAjaxMode();
		
		$use = self::getArg("use", AT_bool, true);
		
		$this->game->actUseCerberusToken($use);
		
		self::ajaxResponse( );
	}
	
	public function actExploitBoar()
	{
		self::setAjaxMode();
		
		$forgePlayerId = self::getArg("forgePlayerId", AT_int, true);
		
		$this->game->actExploitBoar($forgePlayerId);
		
		self::ajaxResponse( );
	}
	
	public function actReinforcementPass()
	{
		self::setAjaxMode();
		
		$this->game->actReinforcementPass();
		self::ajaxResponse( );
	}

	public function actForgeShipPass()
	{
		self::setAjaxMode();
		
		$sideNum = self::getArg( "sideNum", AT_int, true );
		
		$this->game->actForgeShipPass($sideNum);
		self::ajaxResponse( );
	}
	
	public function actForgeNymphPass() {
		self::setAjaxMode();
		
		//$sideNum = self::getArg( "sideNum", AT_int, true );
		
		$this->game->actForgeNymphPass();
		self::ajaxResponse( );
	}
	
	public function actActionChoice()
	{
		self::setAjaxMode();
		
		$action = self::getArg( "actionChoice", AT_alphanum, true );
		$die = self::getArg( "die", AT_int, false );
		
		$this->game->actActionChoice($action, $die);
		self::ajaxResponse( );
	}

	public function actUseScepter() 
	{
		self::setAjaxMode();
		$scepter_id = self::getArg("scepter_id", AT_int, true);
		$resource = self::getArg("resource_type", AT_alphanum, true);

		$this->game->actUseScepter($scepter_id, $resource);

		self::ajaxResponse( );
	}
	
	public function actCancelAllScepters()
	{
		self::setAjaxMode();
		
		$this->game->actCancelAllScepters();

		self::ajaxResponse( );
	}

	public function actDoeTakeRessource() 
	{
		self::setAjaxMode();
		
		$side           = self::getArg( "side", AT_alphanum, true );
		$side_gold      = self::getArg( "side-gold", AT_int, false );
		$side_hammer    = self::getArg( "side-hammer", AT_int, false );
		$side_vp        = self::getArg( "side-vp", AT_int, false );
		$side_moonshard = self::getArg( "side-moonshard", AT_int, false );
		$side_fireshard = self::getArg( "side-fireshard", AT_int, false );
		$side_loyalty   = self::getArg( "side-loyalty", AT_int, false );
		$side_ancientshard   = self::getArg( "side-ancientshard", AT_int, false );
		$side_maze   = self::getArg( "side-maze", AT_int, false );
		$sideNum        = self::getArg( "sideNum", AT_int, true );
		
		$ressources = array(
				'gold'      => $side_gold,
				'vp'        => $side_vp,
				'hammer'    => $side_hammer,
				'moonshard' => $side_moonshard,
				'fireshard' => $side_fireshard,
				'ancientshard' => $side_ancientshard,
				'loyalty'	=> $side_loyalty,
				'maze'		=> $side_maze
		);
		
		$this->game->actDoeTakeRessource($sideNum, $side, $ressources);

		self::ajaxResponse( );
	}

	function actBuyExploit() {
		self::setAjaxMode();
		$card_id = self::getArg( "card_id", AT_int, true );
		$this->game->actBuyExploit($card_id);
		
		self::ajaxResponse( );
	}
	
	function actUseCompanion() {
		self::setAjaxMode();
		$card_id = self::getArg( "card_id", AT_int, true );
		$this->game->actUseCompanion($card_id);
		
		self::ajaxResponse( );
	}

	function actBuyForge() {
		self::setAjaxMode();
		$toForge   = self::getArg( "sideToForge", AT_int, true );
		$toReplace = self::getArg( "sideToReplace", AT_int, true );
		$this->game->actBuyForge($toForge, $toReplace);
		
		self::ajaxResponse( );
	}

	function actEndForge() {
		self::setAjaxMode();
		$this->game->actEndForge();
		self::ajaxResponse( );
	}
	
	//function actForgeDice() {
	//	self::setAjaxMode();
	//	$sides = self::getArg( "sides", AT_numberlist, true );
	//	$forges = explode(";", $sides);
	//	
	//	$result = array();
	//	foreach ($forges as $forge) {
	//		$args = explode(",", $forge);
	//		$result[ $args[0] ] = array("old_side" => $args[1], "dice_number" => $args[2]);
	//	}
	//	$this->game->actForgeDice($result);
	//	
	//	self::ajaxResponse( );
	//}
	
	//function actExploitForging() {
	//	self::setAjaxMode();
	//	$sides = self::getArg( "sides", AT_numberlist, true );
	//	$forges = explode(";", $sides);
	//	
	//	$result = array();
	//	foreach ($forges as $forge) {
	//		$args = explode(",", $forge);
	//		$result[ $args[0] ] = array("old_side" => $args[1], "dice_number" => $args[2]);
	//	}
	//	$this->game->actExploitForging($result);
	//	
	//	self::ajaxResponse( );
	//}
	
	function actSecondAction() {
		self::setAjaxMode();
		
		$play = self::getArg( "play", AT_bool, true );
		
		$this->game->actSecondAction($play);
		self::ajaxResponse( );
	}

	function actEndPlayerTurn() {
		self::setAjaxMode();
		$this->game->actEndPlayerTurn();
		self::ajaxResponse( );
	}
	
	function actExploitEnigma() {
		self::setAjaxMode();
		$die_number = self::getArg( "die_number", AT_int, true );
		$this->game->actExploitEnigma($die_number);
		self::ajaxResponse( );
	}

	// DEBUG TO REMOVE
	function debugResourcesAll() 
	{
		self::setAjaxMode();
		$this->game->debugRessourcesAll();
		self::ajaxResponse( );
  	}
}
  

