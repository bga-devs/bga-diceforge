{OVERALL_GAME_HEADER}

<!-- 
--------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- diceforge implementation : © Thibaut Brissard <docthib@hotmail.com> & Vincent Toper <vincent.toper@gmail.com>
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-------

    diceforge_diceforge.tpl
-->

<script type="text/javascript">

// Javascript HTML templates

	var jstpl_player_ressource='\
	<div id="ressources_container_p${id}" class="ressources-container">\
		<div id="gold_container_p${id}" class="ressource-container">\
			<div class="ressources-small ressources-gold" id="gold_p${id}"></div><span id="goldcount_p${id}" class="ressource-display">0</span><span class="text-small">/<span id="gold_max_p${id}">${gold_max}</span></span>\
		</div>\
		<div id="fire_container_p${id}" class="ressource-container">\
			<div class="ressources-small ressources-fire" id="fire_p${id}"></div><span id="firecount_p${id}" class="ressource-display">0</span><span class="text-small">/<span id="fire_max_p${id}">${fire_max}</span></span>\
			<span id="scepter_fire_${id}" class="hide"></span>\
		</div>\
		<div id="moon_container_p${id}" class="ressource-container">\
			<div class="ressources-small ressources-moon" id="moon_p${id}"></div><span id="mooncount_p${id}" class="ressource-display">0</span><span class="text-small">/<span id="moon_max_p${id}">${moon_max}</span></span>\
			<span id="scepter_moon_${id}" class="hide"></span>\
		</div>\
	</div>';

	var jstpl_hammer = '\
		<div id="hammer_container_p${id}" class="ressource-container ressource-container-hammer hide">\
			<div class="ressources-small ressources-hammer" id="hammer_p${id}"></div><span id="hammercount_p${id}" class="ressource-display">0</span><span class="text-small">/<span id="hammer_max_p${id}">15</span></span>\
			<span id="hammersleft_p${id}" class="hammer-left hide">${remainingHammer}</span>\
			<span id="hammers_p${id}" class="hide">${nbHammer}</span>\
		</div>\
	';

	var jstpl_ancient_shard ='\
		<div id="ancient_shard_container_p${id}" class="ressource-container ressource-container-ancient-shard">\
			<div class="ressources-small ressources-ancient-shard" id="ancient_shard_p${id}"></div><span id="ancientshardcount_p${id}" class="ressource-display"></span><span class="text-small">/<span id="ancient_shard_max_p${id}">6</span></span>\
		</div>\
	';

	var jstpl_overlay = '<div id="df-overlay" class="df-overlay"></div>';
	
	var jstpl_ressource    = '<div class="ressources-${size} ressources-${type}" alt="${type}"></div>';
	var jstpl_ressource_id = '<div class="ressources-${size} ressources-${type}" id="${id}" alt="${type}"></div>';

	var jstpl_power  = '<div id="power-${id}" class="powers-small power-${type}"></div>';

	var jstpl_token         = '<div class="token-${size} token-${type}" alt="${type}"></div>';
	var jstpl_token_id      = '<div id="token-${type}-${player_id}-${num}" class="token-${size} token-${type}"></div>';
	var jstpl_token_scepter = '<div id="token-${type}-${player_id}-${num}" class="token-${size} token-${type}"><span id=countscepter_${num} class="ressource-display">0</span></div>';
	var jstpl_memory_id     = '<div id="${id}" class="token-small ${type}"></div>';
	
	var jstpl_player_pawn = '<div class="pawn ${color}" id="player_${color}"></div>';
	var jstpl_golem = '<span class="token-small token-${color}-golem golem" id="${color}-golem"></span>';
	var jstpl_player_maze_info = '<div>${golem} <span class="player-${color}">${name}</span></div>';
	
	var jstpl_titan_player = '<span class="token-small token-${color}-player player" id="${color}-player"></span>';
	
	var jstpl_chest_position = '<span id="chestcount_p${id}" class="ressource-display">${nbchest}</span>';
	
	var jstpl_turn_order = '<div class="turn-order">${order}</div>';
	var firstPlayerID = {firstPlayerId};

	var jstpl_dice = '\
	<div id="side_container_${player_id}_${dice}_1" class="side side1 die-lining die-lining-${type}" data-numside="1"> <div id="side_${0.id}" class="bside ${0.class}" data-type="${0.type}"></div> </div>\
	<div id="side_container_${player_id}_${dice}_5" class="side side5 die-lining die-lining-${type}" data-numside="5"> <div id="side_${4.id}" class="bside ${4.class}" data-type="${4.type}"></div> </div>\
	<div id="side_container_${player_id}_${dice}_3" class="side side3 die-lining die-lining-${type}" data-numside="3"> <div id="side_${2.id}" class="bside ${2.class}" data-type="${2.type}"></div> </div>\
	<div id="side_container_${player_id}_${dice}_4" class="side side4 die-lining die-lining-${type}" data-numside="4"> <div id="side_${3.id}" class="bside ${3.class}" data-type="${3.type}"></div> </div>\
	<div id="side_container_${player_id}_${dice}_6" class="side side6 die-lining die-lining-${type}" data-numside="6"> <div id="side_${5.id}" class="bside ${5.class}" data-type="${5.type}"></div> </div>\
	<div id="side_container_${player_id}_${dice}_2" class="side side2 die-lining die-lining-${type}" data-numside="2"> <div id="side_${1.id}" class="bside ${1.class}" data-type="${1.type}"></div> </div>';
	
	var jstpl_celestial_dice = '\
	<div class="dice-result-celes"><div class="dice celestial-dice" id="celestial_dice">\
	<div id="celestial_side_container_1" class="side celestial-side side1" data-numside="1"> <div id="side_${0.id}" class="bside ${0.class}" data-type="${0.type}"></div> </div>\
	<div id="celestial_side_container_5" class="side celestial-side side5" data-numside="5"> <div id="side_${4.id}" class="bside ${4.class}" data-type="${4.type}"></div> </div>\
	<div id="celestial_side_container_3" class="side celestial-side side3" data-numside="3"> <div id="side_${2.id}" class="bside ${2.class}" data-type="${2.type}"></div> </div>\
	<div id="celestial_side_container_4" class="side celestial-side side4" data-numside="4"> <div id="side_${3.id}" class="bside ${3.class}" data-type="${3.type}"></div> </div>\
	<div id="celestial_side_container_6" class="side celestial-side side6" data-numside="6"> <div id="side_${5.id}" class="bside ${5.class}" data-type="${5.type}"></div> </div>\
	<div id="celestial_side_container_2" class="side celestial-side side2" data-numside="2"> <div id="side_${1.id}" class="bside ${1.class}" data-type="${1.type}"></div> </div>\
	</div></div>';
	
	var jstpl_dice_flat = '\
	<div id="side_flat_${0.id}" class="bside ${0.class}" data-type="${0.type}"></div>\
	<div id="side_flat_${4.id}" class="bside ${4.class}" data-type="${4.type}"></div>\
	<div id="side_flat_${2.id}" class="bside ${2.class}" data-type="${2.type}"></div>\
	<div id="side_flat_${3.id}" class="bside ${3.class}" data-type="${3.type}"></div>\
	<div id="side_flat_${5.id}" class="bside ${5.class}" data-type="${5.type}"></div>\
	<div id="side_flat_${1.id}" class="bside ${1.class}" data-type="${1.type}"></div>';

	var jstpl_bside_icon = '<div class="bside ${class}" data-type="${type}" alt="${type}"></div>';
	var jstpl_maze_icon = '<div class="${class}" data-type="${type}" alt="${type}"></div>';
	var jstpl_bside      = '<div id="side_${id}" class="bside ${class}" data-type="${type}"></div>';
	var jstpl_flat_bside = '<div id="side_flat_${id}" class="bside ${class}" data-type="${type}"></div>';

	var jstpl_dice_selector      = '<div id="dice-selector" class="${classes}">${html}</div>';
	var jstpl_dice_selector_item = '<div id="dice-num-${player_id}-${dice}" class="item ${classes}"><div class="caption" style="background-color:#${player_color}; border-left: 2px solid #${player_color}; border-right: 2px solid #${player_color};">${dice}</div><span class="num"></span>${html}</div>';
	
	var jstpl_ressource_selector           = '<div id="ressource-selector">${html}</div>';
	var jstpl_ressource_selector_row       = '<div class="ressource-row"><div class="source">${source}</div> <div class="ressources">${ressources}</div></div>';
	var jstpl_ressource_selector_ressource = '<div class="ressource-item">${html}</div>';

	var jstpl_final_card_container = '<div id="final-card-p${id}" class="final-card-container"></div>';

	var jstpl_card_number = '<div class="card-counter white-text-shadowed" id="card-counter-${slot}">${nb}</div>';

	var jstpl_bga_btn = '<button class="bgabutton bgabutton_${color} ${classes}" id="${id}" type="button">${text}</button>';

	var jstpl_tooltip_card = '<div class="tooltip-container">\
		<span class="tooltip-title">${title}</span>\
		<span class="tooltip-message">${cost}</span>\
		<span class="tooltip-message">${vp}</span>\
		<hr/>\
		<span class="tooltip-message">${description}</span>\
	</div>';
	
	var jstpl_tooltip_title = '<div class="tooltip-container">\
		<span class="tooltip-title">${title}</span>\
		<hr/>\
		<span class="tooltip-message">${description}</span>\
	</div>';
	
	var jstpl_tooltip_side = '<div class="tooltip-container">\
		<span class="tooltip-message">${description}</span>\
	</div>';
	
	var jstpl_tooltip_maze = '<div class="tooltip-container">\
		${icon}\
		<span class="tooltip-message">${description}</span>\
	</div>';
	var jstpl_maze_element = '<div class="maze-${size} ${type}" alt="${type}"></div>';
	
	var jstpl_tooltip_classic = '<div class="tooltip-container"><span class="tooltip-title">${title}</span></div>';
	
	var jstpl_discarded_sides_counter = '<div class="discarded-sides white-text-shadowed" id="discarded_sides_p{id}">0</div>';
	var jstpl_turn_count = '<div>${title} <span id="current-turn-number">${turnCount}</span>/<span id="last-turn-number">${nbTurns}</span></div>';
	
	//var jstpl_maze_reward = '<div id="maze-reward-${location}" class="token-small maze-reward"></div>';
</script>  


<div>
	<div class="whiteblock hide" id="draft-container">
		<div id="draft-stock"></div>
	</div>
	<!--<div id="debug_resources" class="bgabutton bgabutton_blue">Ressources Power</div> -->
	<!-- DEBUG TO REMOVE -->
	<div class="fixed-center"> </div>

	<div class='container-play-area'>
		<div class="current-player-play-area play-area whiteblock" id="player-container-{currentPlayerId}">
			<h3 style="color:#{yourColor}">{yourName}</h3>
			<div class='info-container'>
				<div class="ressources-small ressources-2nd-action hide" id="action_p{currentPlayerId}"></div>
				<div class="ressources-small ressources-nb-sides" id="container_discarded_sides_p{currentPlayerId}"><div class="discarded-sides white-text-shadowed" id="discarded_sides_p{currentPlayerId}">0</div></div>
			</div>

			<div class="cards-container">
				<div class="cards-pile-container"> <div class="cards-pile" id="pile-{currentPlayerId}"><div class="card-counter white-text-shadowed" id="card_counter_p{currentPlayerId}">0</div></div> </div>
				<div id="see-discard"><span class="fa fa-chevron-up"></span></div>
			</div>

			<div id="first-flex-container-{currentPlayerId}" class="first-flex-container">
				<!-- Here will come the ressources container-->

				<div class="dices-container">
					<div class="dices-sub-container">
						<div class='dice-flat' id='dice1-flat-player-{currentPlayerId}'>
							<div id="player-{currentPlayerId}-dice-1"></div>
						</div>
						<div class="wrapper-small"></div>
						<div class="dice-result" id="dice1-result-player-{currentPlayerId}">
							<div class="dice current-player-dice" id="player-{currentPlayerId}-dice-3D-1"> </div>
						</div>

						<div class="dice-result" id="dice2-result-player-{currentPlayerId}">
							<div class="dice current-player-dice" id="player-{currentPlayerId}-dice-3D-2"> </div>
						</div>
						<div class="wrapper-big"></div>
						<div class='dice-flat' id='dice2-flat-player-{currentPlayerId}'>
							<div class="" id="player-{currentPlayerId}-dice-2"> </div>
						</div>
					</div>
				</div>

				<div class="blank-container"></div>
			</div>

			<div class="action-row">
				<div class="action-bar">
					<div class="header-action"><span class="ressources-icon ressources-type-cog"></span></div>
		    		<div id="powers_p{currentPlayerId}" class="powers-container"></div>
				</div>
		    	<div class="action-bar">
		    		<div class="header-action"><span class="token-symbol"></span></div>
		    		<div id="tokens_p{currentPlayerId}" class="powers-container"></div>
		    	</div>
		    </div>

			<div class="clear"></div>
		</div>
		<!-- BEGIN players-dice -->
		<div class="play-area whiteblock" id="player-container-{PLAYER_ID}">
			<h3 style="color:#{PLAYER_COLOR}">{PLAYER_NAME}</h3>
			<div class='info-container'>
				<div class="ressources-small ressources-2nd-action hide" id="action_p{PLAYER_ID}"></div>
				<div class="ressources-small ressources-nb-sides" id="container_discarded_sides_p{PLAYER_ID}"><div class="discarded-sides white-text-shadowed" id="discarded_sides_p{PLAYER_ID}">0</div></div>
			</div>

			<div class="cards-container">
				<div class="cards-pile-container"> <div class="cards-pile" id="pile-{PLAYER_ID}"><div class="card-counter white-text-shadowed" id="card_counter_p{PLAYER_ID}">0</div></div> </div>
			</div>

			<div id="first-flex-container-{PLAYER_ID}" class="first-flex-container">
				<!-- Here will come the ressources container-->

				<div class="dices-container">
					<div class="dices-sub-container">
						<div class='dice-flat' id="dice1-flat-player-{PLAYER_ID}">
							<div class="" id="player-{PLAYER_ID}-dice-1"> </div>
						</div>
						<div class="wrapper-small"></div>
						<div class="dice-result" id="dice1-result-player-{PLAYER_ID}">
							<div class="dice" id="player-{PLAYER_ID}-dice-3D-1"> </div>
						</div>

						<div class="dice-result" id="dice2-result-player-{PLAYER_ID}">
							<div class="dice" id="player-{PLAYER_ID}-dice-3D-2"> </div>
						</div>
						<div class="wrapper-big"></div>
						<div class='dice-flat' id="dice2-flat-player-{PLAYER_ID}">
							<div class="" id="player-{PLAYER_ID}-dice-2"> </div>
						</div>	
					</div>
				</div>

				<div class="blank-container"></div>
			</div>

			<div class="action-row">
				<div class="action-bar">
					<div class="header-action"><span class="ressources-icon ressources-type-cog"></span></div>
		    		<div id="powers_p{PLAYER_ID}" class="powers-container"></div>
				</div>
		    	<div class="action-bar">
		    		<div class="header-action"><span class="token-symbol"></span></div>
		    		<div id="tokens_p{PLAYER_ID}" class="powers-container"></div>
		    	</div>
		    </div>

			<div class="clear"></div>
		</div>
		<!-- END players-dice -->
	</div>

	<div class="clear"></div>
	<div id='turn-container'>
		<div id="nb-turns-container"> </div>
		<div id="turn-order-container"></div>
	</div>
	<div id ="container">
		<div class="board-left">
			<div id="base-pools" class="pools">
				<!-- BEGIN pool -->
				<div id="pool-{POOL_ID}" class="pool"></div>
				<!-- END pool -->
			</div>

			<div id="rebellion-pools" class="pools">
				<div id="pool-16" class="pool"></div>
				<div id="pool-17" class="pool"></div>
				<div id="pool-18" class="pool"></div>
				<div id="pool-19" class="pool"></div>
				<div id="pool-20" class="pool"></div>
			</div>
		</div>

		<div class="board-right">
			<div id="board">
				<!-- BEGIN exploit -->
				<div id="exploit-{CARD_POSITION}" class="exploit-pool" data-costFire="{COST_FIRE}" data-costMoon="{COST_MOON}"></div>
				<!-- END exploit -->
			
				<div class="turn{TURN}" id="turncount"></div>
				
				<div class="position" id="position-1"></div>
				<div class="position" id="position-2"></div>
				<div class="position" id="position-3"></div>
				<div class="position" id="position-4"></div>
				<div class="position" id="position-5"></div>
				<div class="position" id="position-6"></div>
				<div class="position" id="position-7"></div>
				<div class='memory' id='memory-1'></div>
				<div class='memory' id='memory-2'></div>
				<div class='memory' id='memory-3'></div>
				<div class='memory' id='memory-4'></div>
				<div class='memory' id='memory-5'></div>
				<div class='memory' id='memory-6'></div>
				<div class='memory' id='memory-7'></div>

				<div class="position" id="position-init-blue"></div>
				<div class="position" id="position-init-green"></div>
				<div class="position" id="position-init-black"></div>
				<div class="position" id="position-init-orange"></div>
			</div>

			<div id="maze-board">
				<div id="maze-reward-8" class="token-small maze-reward hide" style="top: 28px; left: 427px;"></div>
				<div id="maze-reward-12" class="token-small maze-reward hide" style="top: 97px; left: 357px;"></div>
				<div id="maze-reward-21" class="token-small maze-reward hide" style="top: 153px; left: 382px;"></div>
				<div id="maze-tile-1" class="maze-tile" style="top: 101px; left: 14px;"></div>
				<div id="maze-tile-2" class="maze-tile" style="top: 33px; left: 23px;"></div>
				<div id="maze-tile-3" class="maze-tile" style="top: 33px; left: 91px;"></div>
				<div id="maze-tile-4" class="maze-tile" style="top: 33px; left: 159px;"></div>
				<div id="maze-tile-5" class="maze-tile" style="top: 33px; left: 228px;"></div>
				<div id="maze-tile-6" class="maze-tile" style="top: 33px; left: 297px;"></div>
				<div id="maze-tile-7" class="maze-tile" style="top: 33px; left: 365px;"></div>
				<div id="maze-tile-8" class="maze-tile" style="top: 33px; left: 434px;"></div>
				<div id="maze-tile-9" class="maze-tile" style="top: 101px; left: 89px;"></div>
				<div id="maze-tile-10" class="maze-tile" style="top: 101px; left: 187px;"></div>
				<div id="maze-tile-11" class="maze-tile" style="top: 101px; left: 282px;"></div>
				<div id="maze-tile-12" class="maze-tile" style="top: 101px; left: 363px;"></div>
				<div id="maze-tile-13" class="maze-tile" style="top: 101px; left: 494px;"></div>
				<div id="maze-tile-14" class="maze-tile" style="top: 185px; left: 27px;"></div>
				<div id="maze-tile-15" class="maze-tile" style="top: 158px; left: 77px;"></div>
				<div id="maze-tile-16" class="maze-tile" style="top: 185px; left: 131px;"></div>
				<div id="maze-tile-17" class="maze-tile" style="top: 158px; left: 182px;"></div>
				<div id="maze-tile-18" class="maze-tile" style="top: 185px; left: 234px;"></div>
				<div id="maze-tile-19" class="maze-tile" style="top: 158px; left: 285px;"></div>
				<div id="maze-tile-20" class="maze-tile" style="top: 185px; left: 338px;"></div>
				<div id="maze-tile-21" class="maze-tile" style="top: 158px; left: 388px;"></div>
				<div id="maze-tile-22" class="maze-tile" style="top: 185px; left: 442px;"></div>
				<div id="maze-tile-23" class="maze-tile" style="top: 158px; left: 493px;"></div>
				<div id="maze-tile-24" class="maze-tile" style="top: 21px; left: 493px;"></div>
				<div id="maze-tile-25" class="maze-tile" style="top: 21px; left: 555px;"></div>
				<div id="maze-tile-26" class="maze-tile" style="top: 79px; left: 555px;"></div>
				<div id="maze-tile-27" class="maze-tile" style="top: 134px; left: 555px;"></div>
				<div id="maze-tile-28" class="maze-tile" style="top: 189px; left: 555px;"></div>
				<div id="maze-tile-29" class="maze-tile" style="top: 189px; left: 616px;"></div>
				<div id="maze-tile-30" class="maze-tile" style="top: 130px; left: 616px;"></div>
				<div id="maze-tile-31" class="maze-tile" style="top: 77px; left: 614px;"></div>
				<div id="maze-tile-32" class="maze-tile" style="top: 21px; left: 614px;"></div>
				<div id="maze-tile-33" class="maze-tile" style="top: 23px; left: 671px;"></div>
				<div id="maze-tile-34" class="maze-tile" style="top: 21px; left: 729px;"></div>
				<div id="maze-tile-35" class="maze-tile" style="top: 63px; left: 754px;"></div>
				<div id="maze-tile-36" class="maze-tile" style="top: 125px; left: 703px;"></div>
				<div id="maze-first-finish" class="maze-tile" style="top: 153px; left: 744px;"></div>
				<div id="maze-caption"></div>
			</div>
			<!--<div>
				<input type="range" id="maze-opacity" name="maze-opacity" min="0.1" max="1" step="0.1">
				<label for="maze-opacity">Maze opacity</label>
			</div>-->
			<div id="titan-board">
				<div id="titan-passive-1" class="titan-tooltip" data-ref="titanPassive1" style="top: 29px;left: 35px;width: 187px;"></div>
				<div id="titan-passive-2" class="titan-tooltip" data-ref="titanPassive2" style="top: 29px;left: 227px; width: 85px;"></div>
				<div id="loyalty-passive-1" class="titan-tooltip" data-ref="loyaltyPassive1" style="top: 29px;left: 493px;width: 77px;"></div>
				<div id="loyalty-passive-2" class="titan-tooltip" data-ref="loyaltyPassive2" style="top: 29px;left: 575px;width: 45px;"></div>
				<div id="loyalty-passive-3" class="titan-tooltip" data-ref="loyaltyPassive3" style="top: 29px;left: 624px;width: 136px;"></div>


				<div id="titan-tile-1" class="maze-tile" style="top: 175px; left: 13px;"></div>
				<div id="titan-tile-2" class="maze-tile" style="top: 99px; left: 35px;"></div>
				<div id="titan-tile-3" class="maze-tile" style="top: 175px; left: 61px;"></div>
				<div id="titan-tile-4" class="maze-tile" style="top: 99px; left: 83px;"></div>
				<div id="titan-tile-5" class="maze-tile" style="top: 175px; left: 109px;"></div>
				<div id="titan-tile-6" class="maze-tile" style="top: 99px; left: 132px;"></div>
				<div id="titan-tile-7" class="maze-tile" style="top: 175px; left: 158px;"></div>
				<div id="titan-tile-8" class="maze-tile" style="top: 99px; left: 180px;"></div>
				<div id="titan-tile-9" class="maze-tile" style="top: 175px; left: 206px;"></div>
				<div id="titan-tile-10" class="maze-tile" style="top: 99px; left: 228px;"></div>
				<div id="titan-tile-11" class="maze-tile" style="top: 175px; left: 254px;"></div>
				<div id="titan-tile-12" class="maze-tile" style="top: 99px; left: 276px;"></div>
				<div id="titan-tile-13" class="maze-tile" style="top: 175px; left: 302px;"></div>
				<div id="titan-tile-14" class="maze-tile" style="top: 99px; left: 325px;"></div>
				<div id="titan-tile-15" class="maze-tile" style="top: 175px; left: 351px;"></div>
				<div id="titan-tile-16" class="maze-tile start-tile" style="top: 121px; left: 381px;"></div>
				<div id="titan-tile-17" class="maze-tile" style="top: 175px; left: 409px;"></div>
				<div id="titan-tile-18" class="maze-tile" style="top: 99px; left: 432px;"></div>
				<div id="titan-tile-19" class="maze-tile" style="top: 175px; left: 458px;"></div>
				<div id="titan-tile-20" class="maze-tile" style="top: 99px; left: 480px;"></div>
				<div id="titan-tile-21" class="maze-tile" style="top: 175px; left: 506px;"></div>
				<div id="titan-tile-22" class="maze-tile" style="top: 99px; left: 528px;"></div>
				<div id="titan-tile-23" class="maze-tile" style="top: 175px; left: 554px;"></div>
				<div id="titan-tile-24" class="maze-tile" style="top: 99px; left: 576px;"></div>
				<div id="titan-tile-25" class="maze-tile" style="top: 175px; left: 602px;"></div>
				<div id="titan-tile-26" class="maze-tile" style="top: 99px; left: 625px;"></div>
				<div id="titan-tile-27" class="maze-tile" style="top: 175px; left: 651px;"></div>
				<div id="titan-tile-28" class="maze-tile" style="top: 99px; left: 673px;"></div>
				<div id="titan-tile-29" class="maze-tile" style="top: 175px; left: 699px;"></div>
				<div id="titan-tile-30" class="maze-tile" style="top: 99px; left: 721px;"></div>
				<div id="titan-tile-31" class="maze-tile" style="top: 175px; left: 747px;"></div>

			</div>
		</div>
	</div>

	<div class="clear"></div>
</div>
<!--<div>
	<h3>Ressources</h3>
	<span class="ressources-side ressources-gold" title="ressources-gold" alt="ressources-gold"></span>
	<span class="ressources-side ressources-fire" title="ressources-fire" alt="ressources-fire"></span>
	<span class="ressources-side ressources-moon" title="ressources-moon" alt="ressources-moon"></span>
	<span class="ressources-side ressources-vp" title="ressources-vp" alt="ressources-vp"></span>
	<span class="ressources-side ressources-hammer" title="ressources-hammer" alt="ressources-hammer"></span>
	<span class="ressources-side ressources-2nd-action" title="ressources-2nd-action" alt="ressources-2nd-action"></span>
	<span class="ressources-side ressources-100vp" title="ressources-100vp" alt="ressources-100vp"></span>
	<span class="ressources-side ressources-exploit" title="ressources-exploit" alt="ressources-exploit"></span>
	<span class="ressources-side ressources-oust" title="ressources-oust" alt="ressources-oust"></span>
	<span class="ressources-side ressources-action" title="ressources-action" alt="ressources-action"></span>
	<span class="ressources-side ressources-type-instant" title="ressources-type-instant" alt="ressources-type-instant"></span>
	<span class="ressources-side ressources-type-hourglass" title="ressources-type-hourglass" alt="ressources-type-hourglass"></span>
	<span class="ressources-side ressources-type-cog" title="ressources-type-cog" alt="ressources-type-cog"></span>
	<span class="ressources-side ressources-major-blessing" title="ressources-major-blessing" alt="ressources-major-blessing"></span>
	<span class="ressources-side ressources-minor-blessing" title="ressources-minor-blessing" alt="ressources-minor-blessing"></span>
	<span class="ressources-side ressources-2p" title="ressources-2p" alt="ressources-2p"></span>
	<span class="ressources-side ressources-3p" title="ressources-3p" alt="ressources-3p"></span>
	<span class="ressources-side ressources-4p" title="ressources-4p" alt="ressources-4p"></span>
	<span class="ressources-side ressources-localisation" title="ressources-localisation" alt="ressources-localisation"></span>
	<span class="ressources-side ressources-first" title="ressources-first" alt="ressources-first"></span>
	<span class="ressources-side ressources-effect-bear" title="ressources-effect-bear" alt="ressources-effect-bear"></span>
	<span class="ressources-side ressources-effect-red-boar" title="ressources-effect-red-boar" alt="ressources-effect-red-boar"></span>
	<span class="ressources-side ressources-effect-green-boar" title="ressources-effect-green-boar" alt="ressources-effect-green-boar"></span>
	<span class="ressources-side ressources-effect-yellow-boar" title="ressources-effect-yellow-boar" alt="ressources-effect-yellow-boar"></span>
	<span class="ressources-side ressources-effect-blue-boar" title="ressources-effect-blue-boar" alt="ressources-effect-blue-boar"></span>
	<span class="ressources-side ressources-nb-sides" title="ressources-nb-sides" alt="ressources-nb-sides"></span>
	<span class="ressources-side ressources-type-instant-plus-one" title="ressources-type-instant-plus-one" alt="ressources-type-instant-plus-one"></span>
	<span class="ressources-side ressources-effect-green-misfortune" title="ressources-effect-green-misfortune" alt="ressources-effect-green-misfortune"></span>
	<span class="ressources-side ressources-effect-red-misfortune" title="ressources-effect-red-misfortune" alt="ressources-effect-red-misfortune"></span>
	<span class="ressources-side ressources-effect-blue-misfortune" title="ressources-effect-blue-misfortune" alt="ressources-effect-blue-misfortune"></span>
	<span class="ressources-side ressources-effect-yellow-misfortune" title="ressources-effect-yellow-misfortune" alt="ressources-effect-yellow-misfortune"></span>
	<span class="ressources-side ressources-effect-yellow-memory" title="ressources-effect-yellow-memory" alt="ressources-effect-yellow-memory"></span>
	<span class="ressources-side ressources-effect-blue-memory" title="ressources-effect-blue-memory" alt="ressources-effect-blue-memory"></span>
	<span class="ressources-side ressources-effect-red-memory" title="ressources-effect-red-memory" alt="ressources-effect-red-memory"></span>
	<span class="ressources-side ressources-effect-green-memory" title="ressources-effect-green-memory" alt="ressources-effect-green-memory"></span>
	<span class="ressources-side ressources-twins" title="ressources-twins" alt="ressources-twins"></span>
	<span class="ressources-side ressources-effect-twins" title="ressources-effect-twins" alt="ressources-effect-twins"></span>
	<span class="ressources-side ressources-ancient-shard" title="ressources-ancient-shard" alt="ressources-ancient-shard"></span>
	<span class="ressources-side ressources-loyalty" title="ressources-loyalty" alt="ressources-loyalty"></span>
	<h3>Tokens</h3>
	<span class="token-small token-cerberus" title="token-cerberus" alt="token-cerberus"></span>
	<span class="token-small token-triton" title="token-triton" alt="token-triton"></span>
	<span class="token-small token-yellow-memory-sun" title="token-yellow-memory-sun" alt="token-yellow-memory-sun"></span>
	<span class="token-small token-blue-memory-sun" title="token-blue-memory-sun" alt="token-blue-memory-sun"></span>
	<span class="token-small token-red-memory-sun" title="token-red-memory-sun" alt="token-red-memory-sun"></span>
	<span class="token-small token-green-memory-sun" title="token-green-memory-sun" alt="token-green-memory-sun"></span>
	<span class="token-small token-yellow-memory-moon" title="token-yellow-memory-moon" alt="token-yellow-memory-moon"></span>
	<span class="token-small token-blue-memory-moon" title="token-blue-memory-moon" alt="token-blue-memory-moon"></span>
	<span class="token-small token-red-memory-moon" title="token-red-memory-moon" alt="token-red-memory-moon"></span>
	<span class="token-small token-green-memory-moon" title="token-green-memory-moon" alt="token-green-memory-moon"></span>
	<span class="token-small token-black-golem" title="token-black-golem" alt="token-black-golem"></span>
	<span class="token-small token-black-player" title="token-black-player" alt="token-black-player"></span>
	<span class="token-small token-blue-golem" title="token-blue-golem" alt="token-blue-golem"></span>
	<span class="token-small token-blue-player" title="token-blue-player" alt="token-blue-player"></span>
	<span class="token-small token-green-golem" title="token-green-golem" alt="token-green-golem"></span>
	<span class="token-small token-green-player" title="token-green-player" alt="token-green-player"></span>
	<span class="token-small token-orange-golem" title="token-orange-golem" alt="token-orange-golem"></span>
	<span class="token-small token-orange-player" title="token-orange-player" alt="token-orange-player"></span>
	<span class="token-small token-maze-fs4" title="token-maze-fs4" alt="token-maze-fs4"></span>
	<span class="token-small token-maze-ms4" title="token-maze-ms4" alt="token-maze-ms4"></span>
	<span class="token-small token-maze-vp10" title="token-maze-vp10" alt="token-maze-vp10"></span>
	<span class="token-small token-maze-fs1" title="token-maze-fs1" alt="token-maze-fs1"></span>
	<span class="token-small token-maze-ms1" title="token-maze-ms1" alt="token-maze-ms1"></span>
	<span class="token-small token-maze-vp2" title="token-maze-vp2" alt="token-maze-vp2"></span>
	<span class="token-small token-companion" title="token-companion" alt="token-companion"></span>
	<span class="token-small token-scepter" title="token-scepter" alt="token-scepter"></span>
	<h3>Powers</h3>
	<span class="powers-small power-owl" title="power-owl" alt="power-owl"></span>
	<span class="powers-small power-ancient" title="power-ancient" alt="power-ancient"></span>
	<span class="powers-small power-doe" title="power-doe" alt="power-doe"></span>
	<span class="powers-small power-nymphe" title="power-nymphe" alt="power-nymphe"></span>
	<span class="powers-small power-tree" title="power-tree" alt="power-tree"></span>
	<span class="powers-small power-oracle" title="power-oracle" alt="power-oracle"></span>
	<span class="powers-small power-light" title="power-light" alt="power-light"></span>
	<span class="powers-small power-guardian" title="power-guardian" alt="power-guardian"></span>
	<span class="powers-small power-merchant" title="power-merchant" alt="power-merchant"></span>
	<span class="powers-small power-companion" title="power-companion" alt="power-companion"></span>
	<span class="powers-small power-companion-0" title="power-companion-0" alt="power-companion-0"></span>
	<span class="powers-small power-companion-1" title="power-companion-1" alt="power-companion-1"></span>
	<span class="powers-small power-companion-2" title="power-companion-2" alt="power-companion-2"></span>
	<span class="powers-small power-companion-3" title="power-companion-3" alt="power-companion-3"></span>
	<span class="powers-small power-companion-4" title="power-companion-4" alt="power-companion-4"></span>
	<span class="powers-small power-companion-5" title="power-companion-5" alt="power-companion-5"></span>
	<h3>Sides</h3>
	<span class="bside side-g1" title="side-g1" alt="side-g1"></span>
	<span class="bside side-g3" title="side-g3" alt="side-g3"></span>
	<span class="bside side-g4" title="side-g4" alt="side-g4"></span>
	<span class="bside side-g6" title="side-g6" alt="side-g6"></span>
	<span class="bside side-fs1" title="side-fs1" alt="side-fs1"></span>
	<span class="bside side-fs2" title="side-fs2" alt="side-fs2"></span>
	<span class="bside side-ms1" title="side-ms1" alt="side-ms1"></span>
	<span class="bside side-ms2" title="side-ms2" alt="side-ms2"></span>
	<span class="bside side-vp2" title="side-vp2" alt="side-vp2"></span>
	<span class="bside side-vp3" title="side-vp3" alt="side-vp3"></span>
	<span class="bside side-vp4" title="side-vp4" alt="side-vp4"></span>
	<span class="bside side-g1-or-fs1-or-ms1" title="side-g1-or-fs1-or-ms1" alt="side-g1-or-fs1-or-ms1"></span>
	<span class="bside side-g2-or-fs2-or-ms2" title="side-g2-or-fs2-or-ms2" alt="side-g2-or-fs2-or-ms2"></span>
	<span class="bside side-g2-plus-ms1" title="side-g2-plus-ms1" alt="side-g2-plus-ms1"></span>
	<span class="bside side-vp1-plus-fs1" title="side-vp1-plus-fs1" alt="side-vp1-plus-fs1"></span>
	<span class="bside side-g3-or-vp2" title="side-g3-or-vp2" alt="side-g3-or-vp2"></span>
	<span class="bside side-g1-plus-vp1-plus-fs1-plus-ms1" title="side-g1-plus-vp1-plus-fs1-plus-ms1" alt="side-g1-plus-vp1-plus-fs1-plus-ms1"></span>
	<span class="bside side-vp2-plus-ms2" title="side-vp2-plus-ms2" alt="side-vp2-plus-ms2"></span>
	<span class="bside side-x3" title="side-x3" alt="side-x3"></span>
	<span class="bside side-mirror" title="side-mirror" alt="side-mirror"></span>
	<span class="bside side-ship" title="side-ship" alt="side-ship"></span>
	<span class="bside side-blue-boar" title="side-blue-boar" alt="side-blue-boar"></span>
	<span class="bside side-yellow-boar" title="side-yellow-boar" alt="side-yellow-boar"></span>
	<span class="bside side-red-boar" title="side-red-boar" alt="side-red-boar"></span>
	<span class="bside side-boar" title="side-boar" alt="side-boar"></span>
	<span class="bside side-green-boar" title="side-green-boar" alt="side-green-boar"></span>
	<span class="bside side-blue-shield" title="side-blue-shield" alt="side-blue-shield"></span>
	<span class="bside side-yellow-shield" title="side-yellow-shield" alt="side-yellow-shield"></span>
	<span class="bside side-green-shield" title="side-green-shield" alt="side-green-shield"></span>
	<span class="bside side-red-shield" title="side-red-shield" alt="side-red-shield"></span>
	<span class="bside side-as1" title="side-as1" alt="side-as1"></span>
	<span class="bside side-l1-plus-v1" title="side-l1-plus-v1" alt="side-l1-plus-v1"></span>
	<span class="bside side-l1-plus-v1-plus-g2" title="side-l1-plus-v1-plus-g2" alt="side-l1-plus-v1-plus-g2"></span>
	<span class="bside side-g3-plus-as1" title="side-g3-plus-as1" alt="side-g3-plus-as1"></span>
	<span class="bside side-titan-blue-shield" title="side-titan-blue-shield" alt="side-titan-blue-shield"></span>
	<span class="bside side-titan-yellow-shield" title="side-titan-yellow-shield" alt="side-titan-yellow-shield"></span>
	<span class="bside side-titan-red-shield" title="side-titan-red-shield" alt="side-titan-red-shield"></span>
	<span class="bside side-titan-green-shield" title="side-titan-green-shield" alt="side-titan-green-shield"></span>
	<span class="bside side-blue-misfortune" title="side-blue-misfortune" alt="side-blue-misfortune"></span>
	<span class="bside side-yellow-misfortune" title="side-yellow-misfortune" alt="side-yellow-misfortune"></span>
	<span class="bside side-red-misfortune" title="side-red-misfortune" alt="side-red-misfortune"></span>
	<span class="bside side-green-misfortune" title="side-green-misfortune" alt="side-green-misfortune"></span>
	<span class="bside side-moon-golem" title="side-moon-golem" alt="side-moon-golem"></span>
	<span class="bside side-sun-golem" title="side-sun-golem" alt="side-sun-golem"></span>
	<span class="bside side-v3-plus-g3-or-fs1-or-ms1" title="side-v3-plus-g3-or-fs1-or-ms1" alt="side-v3-plus-g3-or-fs1-or-ms1"></span>
	<span class="bside side-vp5" title="side-vp5" alt="side-vp5"></span>
	<span class="bside side-g12" title="side-g12" alt="side-g12"></span>
	<span class="bside side-celestial-mirror" title="side-celestial-mirror" alt="side-celestial-mirror"></span>
	<span class="bside side-double-upgrade" title="side-double-upgrade" alt="side-double-upgrade"></span>
	<span class="bside side-choose-side" title="side-choose-side" alt="side-choose-side"></span>
	<h3>Maze</h3>
	<span class="maze-small maze-fs1" title="maze-fs1" alt="maze-fs1"></span>
	<span class="maze-small maze-vp3" title="maze-vp3" alt="maze-vp3"></span>
	<span class="maze-small maze-g6-or-vp3" title="maze-g6-or-vp3" alt="maze-g6-or-vp3"></span>
	<span class="maze-small maze-vp5" title="maze-vp5" alt="maze-vp5"></span>
	<span class="maze-small maze-vp3-fs1-ms1" title="maze-vp3-fs1-ms1" alt="maze-vp3-fs1-ms1"></span>
	<span class="maze-small maze-steal-vp2" title="maze-steal-vp2" alt="maze-steal-vp2"></span>
	<span class="maze-small maze-bonus" title="maze-bonus" alt="maze-bonus"></span>
	<span class="maze-small maze-celestial1" title="maze-celestial1" alt="maze-celestial1"></span>
	<span class="maze-small maze-ship-g2" title="maze-ship-g2" alt="maze-ship-g2"></span>
	<span class="maze-small maze-g6" title="maze-g6" alt="maze-g6"></span>
	<span class="maze-small maze-ms1" title="maze-ms1" alt="maze-ms1"></span>
	<span class="maze-small maze-g3-or-fs1-or-ms1" title="maze-g3-or-fs1-or-ms1" alt="maze-g3-or-fs1-or-ms1"></span>
	<span class="maze-small maze-ship" title="maze-ship" alt="maze-ship"></span>
	<span class="maze-small maze-ms2-or-vp3" title="maze-ms2-or-vp3" alt="maze-ms2-or-vp3"></span>
	<span class="maze-small maze-fs2-or-ms2" title="maze-fs2-or-ms2" alt="maze-fs2-or-ms2"></span>
	<span class="maze-small maze-celestial2" title="maze-celestial2" alt="maze-celestial2"></span>
	<span class="maze-small maze-g6-to-vp6" title="maze-g6-to-vp6" alt="maze-g6-to-vp6"></span>
	<span class="maze-small maze-ms2-to-vp8" title="maze-ms2-to-vp8" alt="maze-ms2-to-vp8"></span>
	<span class="maze-small maze-side-to-vp" title="maze-site-to-vp" alt="maze-site-to-vp"></span>
	<span class="maze-small maze-vp15" title="maze-vp15" alt="maze-vp15"></span>
	<span class="maze-small maze-first-arrived" title="maze-first-arrived" alt="maze-first-arrived"></span>
</div>-->
{OVERALL_GAME_FOOTER}
