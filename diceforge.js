/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * diceforge implementation : © Thibaut Brissard <docthib@hotmail.com> & Vincent Toper <vincent.toper@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * diceforge.js
 *
 * diceforge user interface script
 *
 * In this file, you are describing the logic of your user interface, in Javascript language.
 *
 */

/*jshint -W069 */
define([
    "dojo","dojo/_base/declare", "dojo/_base/lang", "dojo/NodeList-traverse",
    "ebg/core/gamegui",
    "ebg/counter",
    "ebg/stock",
    "ebg/zone"
],
function (dojo, declare) {
    return declare("bgagame.diceforge", ebg.core.gamegui, {
        constructor: function(){
            console.log('diceforge constructor');
            var self = this;
            this.statesInfo    = {};
            this.pools         = [];
            this.zones         = [];
            this.exploits      = [];
            this.isTouchScreen = false;
            this.selectForge   = {
                isInit      : false,
                forgeType   : false,
                sideToForge : false,
                isForging   : false,
                poolList    : null,
                init : function( params ) {
                    // console.log('selectForge.init');
                    // console.debug(params);
                    params           = params == undefined ? {} : params;
                    this.forgeType   = params.forgeType == undefined ? "gold" : params.forgeType;
                    this.sideToForge = params.sideToForge == undefined ? false : params.sideToForge;
                    this.isForging   = params.isForging == undefined ? false : params.isForging;
                    this.poolList    = params.poolList == undefined ? null : params.poolList;
                    this.activatePoolSides();
                    this.isInit = true;
                    // console.log(this);
                },
                end : function() {
                    // console.log('selectForge.end');
                    if ( this.isInit ) {
                        dojo.query("#player-container-" + self.player_id + " .dices-container").removeClass("flat");
                        this.deactivatePoolSides();
                        this.deactivateSelfSides();
                        this.isInit = false;
                    }
                },
                getSideToForge : function() {
                    if ( this.sideToForge )
                        return this.sideToForge;

                    var side = false;
                    for (var pool in self.pools) {
                        if ( self.pools[ pool ].getSelectedItems().length )
                            side = self.pools[ pool ].getSelectedItems()[0].id;
                    }

                    return side;
                },
                getSideToReplace : function() {
                    return dojo.query(".bside.selected").length ? dojo.query(".bside.selected")[0].id.match(/side_flat_([0-9]+)/)[1] : false;
                },
                resetSelection : function() {
                    dojo.query(".bside.selected").removeClass("selected");

                    if (this.sideToForge == undefined || !this.sideToForge) {
                        for (var pool in self.pools) {
                            self.pools[ pool ].unselectAll();
                        }
                    }
                },
                activatePoolSides: function() {
                    // console.log('selectForge.activatePoolSides');
                    switch (this.forgeType) {
                        case "gold":
                            for (var pool in self.pools) {
                                if (pool > 10)
                                    continue;
                                dojo.query("#pool-" + pool).addClass("clickable");
                                self.pools[ pool ].setSelectionMode(1);
                                self.connexions['pool' + pool] = dojo.connect( self.pools[ pool ], 'onChangeSelection', self, 'onClickForgePoolSide' );
                            }
                            break;
                        case "mirror":
                            if (this.sideToForge) {
                                self.pools[11].selectItem(this.sideToForge);
                                this.activateSelfSides();
                            } else {
                                dojo.query("#pool-11").addClass("clickable");
                                self.pools[ 11 ].setSelectionMode(1);
                                self.connexions['pool' + 11] = dojo.connect( self.pools[ 11 ], 'onChangeSelection', self, 'onClickForgePoolSide' );
                            }
                            break;
                        case "shield":
                            dojo.query("#pool-12").addClass("clickable");
                            self.pools[ 12 ].setSelectionMode(1);
                            self.connexions['pool' + 12] = dojo.connect( self.pools[ 12 ], 'onChangeSelection', self, 'onClickForgePoolSide' );
                            break;
                        case "ship":
                            if (this.sideToForge) {
                                self.pools[13].selectItem(this.sideToForge);
                                this.activateSelfSides();
                            }
                            else {
                                dojo.query("#pool-13").addClass("clickable");
                                self.pools[ 13 ].setSelectionMode(1);
                                self.connexions['pool' + 13] = dojo.connect( self.pools[ 13 ], 'onChangeSelection', self, 'onClickForgePoolSide' );
                            }
                            break;
                        case "boar":
                            // Selection of the side linked to the card
                            self.pools[14].selectItem(this.sideToForge);
                            this.activateSelfSides();
                            break;
                        case "triple":
                            if (this.sideToForge) {
                                self.pools[15].selectItem(this.sideToForge);
                                this.activateSelfSides();
                            } else {
                                dojo.query("#pool-15").addClass("clickable");
                                self.pools[ 15 ].setSelectionMode(1);
                                self.connexions['pool' + 15] = dojo.connect( self.pools[ 15 ], 'onChangeSelection', self, 'onClickForgePoolSide' );
                            }
                            break;
                        case "all":
                            for (var pool in self.pools) {
                                dojo.query("#pool-" + pool).addClass("clickable");
                                self.pools[ pool ].setSelectionMode(1);
                                self.connexions['pool' + pool] = dojo.connect( self.pools[ pool ], 'onChangeSelection', self, 'onClickForgePoolSide' );
                            }
                            break ;
                        case "select":
                            for (var poolNum in this.poolList) {
                                pool = this.poolList[poolNum];
                                dojo.query("#pool-" + pool).addClass("clickable");
                                if (self.pools.hasOwnProperty(pool)) {
                                    self.pools[ pool ].setSelectionMode(1);
                                    self.connexions['pool' + pool] = dojo.connect( self.pools[ pool ], 'onChangeSelection', self, 'onClickForgePoolSide' );
                                }
                            }
                            break ;

                        case "moonGolem":
                            if (this.sideToForge) {
                                self.pools[19].selectItem(this.sideToForge);
                                this.activateSelfSides();
                            } else {
                                dojo.query("#pool-19").addClass("clickable");
                                self.pools[ 19 ].setSelectionMode(1);
                                self.connexions['pool' + 19] = dojo.connect( self.pools[ 19 ], 'onChangeSelection', self, 'onClickForgePoolSide' );
                            }
                            break;
                        case "sunGolem":
                            if (this.sideToForge) {
                                self.pools[17].selectItem(this.sideToForge);
                                this.activateSelfSides();
                            } else {
                                dojo.query("#pool-17").addClass("clickable");
                                self.pools[ 17 ].setSelectionMode(1);
                                self.connexions['pool' + 17] = dojo.connect( self.pools[ 17 ], 'onChangeSelection', self, 'onClickForgePoolSide' );
                            }
                            break;
                        case "dogged":
                            dojo.query("#pool-18").addClass("clickable");
                            self.pools[ 18 ].setSelectionMode(1);
                            self.connexions['pool' + 18] = dojo.connect( self.pools[ 18 ], 'onChangeSelection', self, 'onClickForgePoolSide' );
                            break;
                        case "shieldRebellion":
                            dojo.query("#pool-20").addClass("clickable");
                            self.pools[ 20 ].setSelectionMode(1);
                            self.connexions['pool' + 20] = dojo.connect( self.pools[ 20 ], 'onChangeSelection', self, 'onClickForgePoolSide' );
                            break;
                        case "misfortune":
                            // Selection of the side linked to the card
                            self.pools[16].selectItem(this.sideToForge);
                            this.activateSelfSides();
                            break;
                    }
                },
                deactivatePoolSides: function() {
                    // console.log('selectForge.deactivatePoolSidess');
                    for (var pool in self.pools)
                    {
                        if ( self.connexions.hasOwnProperty("pool" + pool) )
                        {
                            dojo.disconnect( self.connexions["pool" + pool] );
                            delete self.connexions["pool" + pool];
                        }
                        self.pools[ pool ].setSelectionMode(0);
                    }
                    dojo.query(".pool").removeClass("clickable");
                    // Bug # 13227
                    dojo.query(".stockitem").removeClass("stockitem_selected");
                },
                activateSelfSides: function() {
                    // console.log('selectForge.activateSelfSides');
                    dojo.query(".current-player-play-area .dice-flat .bside").addClass("clickable");
                    self.connexions['forge'] = dojo.query(".current-player-play-area .bside").map(function(el) {
                        return dojo.connect(el, "onclick", self, 'onClickForgeSelfSide' );
                    });
                },

                deactivateSelfSides: function() {
                    // console.log('selectForge.deactivateSelfSides');
                    dojo.query(".current-player-play-area .bside.clickable").removeClass("clickable");
                    if ( self.connexions.hasOwnProperty("forge") ) {
                        dojo.forEach(self.connexions["forge"], function(el) {
                            dojo.disconnect(el);
                        });
                        delete self.connexions["forge"];
                    }
                },
            };

            this.selfSides = {
                connexions    : false,
                sidesSelected : [],
                selectionMode : '',
                selectionCallback : '',
                activate: function(selectionMode, callback) {
                    this.selectionMode = (selectionMode == undefined) ? 'OneSidePerDice' : selectionMode;
                    this.selectionCallback = (callback == undefined) ? 'onClickConfirmSelfSideSelection' : callback;

                    this.sidesSelected = [];

                    dojo.query(".current-player-play-area .dice-flat .bside").addClass("clickable");

                    this.connexions = dojo.query(".current-player-play-area .bside").map(function(el) {
                        return dojo.connect(el, "onclick", self.selfSides, "onClick" + self.selfSides.selectionMode);
                    });
                },
                deactivate: function() {
                    dojo.query(".current-player-play-area .bside.clickable").removeClass("clickable selected");

                    if (false !== this.connexions) {
                        dojo.forEach(this.connexions, function(el) {
                            dojo.disconnect(el);
                        });

                       this.connexions = false;
                       this.sidesSelected = [];
                    }
                },
                onClickOneSidePerDice: function( event ) {
                    $side = event.target;
                    $parent = $side.parentElement;
                    var sides = [];

                    var diceNum = $parent.id.match(/player-([0-9]+)-dice-([0-9])/)[2];
                    dojo.query( $parent ).query(".bside.selected").removeClass("selected");
                    dojo.query( $side ).addClass("selected");

                    // Identify selected sides
                    dojo.query('.current-player-play-area .bside.selected').forEach(function (el) {
                        var obj = {
                            'id'   : el.id.match(/side_flat_([0-9]+)/)[1],
                            'type' : el.getAttribute('data-type'),
                        }

                        sides.push(obj);
                    });

                    // Store selected sides IDs
                    this.sidesSelected = sides;

                    // If nb of selected sides equal number of dices in HTML then we are done
                    // todo, maybe add a confirm for cautious users
                    if (sides.length == dojo.query('.current-player-play-area .dice-flat').length)
                        self[ this.selectionCallback ]();
                },
                onClickOneSide: function( event ) {
                   $side = event.target;

                   dojo.query('.current-player-play-area').query('.bside.selected').removeClass('selected');
                   dojo.query( $side ).addClass('selected');

                   // Identify selected side
                   var obj = {
                       'id'   : $side.id.match(/side_flat_([0-9]+)/)[1],
                       'type' : $side.getAttribute('data-type'),
                       'diceNum': $side.parentElement.id.match(/player-([0-9]+)-dice-([0-9])/)[2],
                   };

                   // Store selected side
                   this.sidesSelected = [obj];

                   // todo, maybe add a confirm for cautious users
                   self[ this.selectionCallback ]();
               },
            };

            //this.maze = {
            //    boardId: 'maze-board',
            //    positions: {},
            //    rewards: {},
            //    create: function() {
            //        // insert board and elements ?
            //        // create stocks
            //        for (var i = 1 ; i <= 36 ; i++) {
            //            this.positions[i] = {'id':'maze-tile-1'};
            //        }
            //
            //        this.positions.goddess = {'id':'goddess'};
            //        //
            //    },
            //    init: function() {
            //        // players position and their colors
            //        // rewards taken (true/false)
            //        // goddess power taken (true/false)
            //    },
            //    askMove: function(player_id, nb_move) {
            //        // look for player position
            //        // suggest every possible path while highlighting them + rewards
            //        // returns
            //    },
            //    movePlayer: function(player_id, tile_from, tile_to) {
            //
            //    },
            //    swapRewardEffect: function(reward_number) {
            //
            //    }
            //};

            // Position of the side in the sprite ressource
            this.sidePosition = [
                "G1",
                "G3",
                "G4",
                "G6",
                "FS1",
                "FS2",
                "MS1",
                "MS2",
                "V2",
                "V3",
                "V4",
                "1Gor1FSor1MS",
                "2Gor2FSor2MS",
                "G2MS1",
                "V1FS1",
                "G3orV2",
                "G1V1FS1MS1",
                "V2MS2",
                "triple",
                "mirror",
                "ship",
                "ship",
                "blueBoar",
                "yellowBoar",
                "redBoar",
                "greenBoar",
                "blueShield",
                "yellowShield",
                "greenShield",
                "redShield",
                "boar",
                "tritonToken",
                "AS1",
                "L1V1",
                "L1V1G2",
                "G3AS1",
                "titanBlueShield",
                "titanYellowShield",
                "titanRedShield",
                "titanGreenShield",
                "blueMisfortune",
                "yellowMisfortune",
                "greenMisfortune",
                "redMisfortune",
                "moonGolem",
                "sunGolem",
                "G12",
                "V5",
                "V3G3orFS1orMS1",
                "celestialMirror",
                "chooseSide",
                "doubleUpgrade",
            ];

            this.sideClass = {
                'G1'                : 'side-g1',
                'G3'                : 'side-g3',
                'G4'                : 'side-g4',
                'G6'                : 'side-g6',
                'FS1'               : 'side-fs1',
                'FS2'               : 'side-fs2',
                'MS1'               : 'side-ms1',
                'MS2'               : 'side-ms2',
                'V2'                : 'side-vp2',
                'V3'                : 'side-vp3',
                'V4'                : 'side-vp4',
                '1Gor1FSor1MS'      : 'side-g1-or-fs1-or-ms1',
                '2Gor2FSor2MS'      : 'side-g2-or-fs2-or-ms2',
                'G2MS1'             : 'side-g2-plus-ms1',
                'V1FS1'             : 'side-vp1-plus-fs1',
                'G3orV2'            : 'side-g3-or-vp2',
                'G1V1FS1MS1'        : 'side-g1-plus-vp1-plus-fs1-plus-ms1',
                'V2MS2'             : 'side-vp2-plus-ms2',
                'triple'            : 'side-x3',
                'mirror'            : 'side-mirror',
                'ship'              : 'side-ship',
                'blueBoar'          : 'side-blue-boar',
                'yellowBoar'        : 'side-yellow-boar',
                'redBoar'           : 'side-red-boar',
                'greenBoar'         : 'side-green-boar',
                'blueShield'        : 'side-blue-shield',
                'yellowShield'      : 'side-yellow-shield',
                'redShield'         : 'side-red-shield',
                'greenShield'       : 'side-green-shield',
                'boar'              : 'ressources-effect-red-boar',
                'tritonToken'       : 'token-triton-small',
                'AS1'               : 'side-as1',
                'L1V1'              : 'side-l1-plus-v1',
                'L1V1G2'            : 'side-l1-plus-v1-plus-g2',
                'G3AS1'             : 'side-g3-plus-as1',
                'titanBlueShield'   : 'side-titan-blue-shield',
                'titanYellowShield' : 'side-titan-yellow-shield',
                'titanRedShield'    : 'side-titan-red-shield',
                'titanGreenShield'  : 'side-titan-green-shield',
                'blueMisfortune'    : 'side-blue-misfortune',
                'yellowMisfortune'  : 'side-yellow-misfortune',
                'greenMisfortune'   : 'side-green-misfortune',
                'redMisfortune'     : 'side-red-misfortune',
                'otherMisfor'       : 'side-other-misfortune',
                'moonGolem'         : 'side-moon-golem',
                'sunGolem'          : 'side-sun-golem',
                'G12'               : 'side-g12',
                'V5'                : 'side-vp5',
                'V3G3orFS1orMS1'    : 'side-v3-plus-g3-or-fs1-or-ms1',
                'celestialMirror'   : 'side-celestial-mirror',
                'chooseSide'        : 'side-choose-side',
                'doubleUpgrade'     : 'side-double-upgrade',
                'twins'             : 'ressources-twins'
            };

            this.mazeClass = {
                'mFS1'              : 'maze-fs1',
                'mV3'               : 'maze-vp3',
                'mG6orV3'           : 'maze-g6-or-vp3',
                'mV5'               : 'maze-vp5',
                'mFS1MS1V3'         : 'maze-vp3-fs1-ms1',
                'mSteal2VP'         : 'maze-steal-vp2',
                'mTreasure'         : 'maze-bonus',
                'mCelestial'        : 'maze-celestial1',
                'mcelestialRoll'    : 'maze-celestial1',
                'mShip'             : 'maze-ship-g2',
                'mG6'               : 'maze-g6',
                'mMS1'              : 'maze-ms1',
                'mG3orMS1orFS1'     : 'maze-g3-or-fs1-or-ms1',
                'mForge'            : 'maze-ship',
                'mMS2orV3'          : 'maze-ms2-or-vp3',
                'mMS2orFS2'         : 'maze-fs2-or-ms2',
                'mCelestial2'       : 'maze-celestial2',
                'mcelestialRollx2'  : 'maze-celestial2',
                //'mtoto'           : 'maze-g6-to-vp6',
                //'mtiti'           : 'maze-ms2-to-vp8',
                'mV15'              : 'maze-vp15',
                'mfirstFinish'      : 'maze-first-finish',
                'msteal2VP'         : 'maze-steal-vp2',
                'mtreasure'         : 'maze-bonus',
                'mforgeShip'        : 'maze-ship-g2',
                'mforge'            : 'maze-ship',
                'mconvert6Gto6VP'   : 'maze-g6-to-vp6',
                'mconvertMS2to8VP'  : 'maze-ms2-to-vp8',
                'mnone'             : 'hide',
                'mscoreForgedSides' : 'maze-side-to-vp',
                'm15VP'             : 'maze-vp15',
                'mstart'            : 'maze-start',
                'maze-fs1'          : 'token-small token-maze-fs1',
                'maze-ms1'          : 'token-small token-maze-ms1',
                'maze-vp2'          : 'token-small token-maze-vp2',
            };

            this.classEffect = {
                'bear'             : 'effect-bear',
                'redBoar'          : 'effect-red-boar',
                'greenBoar'        : 'effect-green-boar',
                'yellowBoar'       : 'effect-yellow-boar',
                'blueBoar'         : 'effect-blue-boar',
                'blueMisfortune'   : 'effect-blue-misfortune',
                'yellowMisfortune' : 'effect-yellow-misfortune',
                'greenMisfortune'  : 'effect-green-misfortune',
                'redMisfortune'    : 'effect-red-misfortune',
                //'blueMemory'       : 'effect-blue-memory',
                //'yellowMemory'     : 'effect-yellow-memory',
                //'greenMemory'      : 'effect-green-memory',
                //'redMemory'        : 'effect-red-memory',
                'twins'            : 'effect-twins',
            }

            this.poolCost = {
                "1"  : 2,
                "2"  : 2,
                "3"  : 3,
                "4"  : 3,
                "5"  : 4,
                "6"  : 5,
                "7"  : 6,
                "8"  : 8,
                "9"  : 8,
                "10" : 12,
            };

            this.memoryMap = {
                'blueMemory'       : 'token-blue-memory-',
                'yellowMemory'     : 'token-yellow-memory-',
                'greenMemory'      : 'token-green-memory-',
                'redMemory'        : 'token-red-memory-',
            };

            // Management of the exploit
            // List of the position of the board to fill. If extension, managed by the setup routine in game.php
            this.exploitSlot   = ["M1","M2","M3","M4","M5","M6","M7","M8","F1","F2","F3","F4","F5","F6","F7"];
            this.exploitWidth  = 93;
            this.exploitHeight = 144;
            this.exploitByRow  = 11;
            // Exploit position in the sprite ressource
            this.exploitSpritePosition = {
                "ancient"          : 0,
                "grass"            : 1,
                "owl"              : 2,
                "minotaure"        : 3,
                "medusa"           : 4,
                "mirror"           : 5,
                "enigma"           : 6,
                "hydra"            : 7,
                "claw"             : 8,
                "invisible"        : 9,
                "passeur"          : 10,
                "satyres"          : 11,
                "doe"              : 12,
                "chest"            : 13,
                "hammer"           : 14,
                "ship"             : 15,
                "shield"           : 16,
                "triton"           : 17,
                "cyclops"          : 18,
                "typhon"           : 19,
                "sentinel"         : 20,
                "cerberus"         : 21,
                "greenBoar"        : 22,
                "blueBoar"         : 23,
                "yellowBoar"       : 24,
                "redBoar"          : 25,
                "bear"             : 26,
                "harpy"            : 27,
                "chimera"          : 28,
                "monsterMother"    : 29,
                "nymphe"           : 30,
                "magicSeagull"     : 31,
                "hydraPromo"       : 32,
                "tree"             : 33,
                "woodNymph"        : 34,
                "sunGolem"         : 35,
                "timeGolem"        : 36,
                "goldsmith"        : 37,
                "trident"          : 38,
                "eternalFire"      : 39,
                "goddess"          : 40,
                "eternalNight"     : 41,
                "greatGolem"       : 42,
                "mists"            : 43,
                "celestial"        : 44,
                "moonGolem"        : 45,
                "companion"        : 46,
                "twins"            : 47,
                "merchant"         : 48,
                "dogged"           : 49,
                "guardian"         : 50,
                "light"            : 51,
                "omniscient"       : 52,
                "yellowMisfortune" : 53,
                "leftHand"         : 54,
                "titan"            : 55,
                "rightHand"        : 56,
                "chaos"            : 57,
                "ancestor"         : 58,
                "wind"             : 59,
                "oracle"           : 60,
                "greenMemory"      : 61,
                "yellowMemory"     : 62,
                "blueMemory"       : 63,
                "redMemory"        : 64,
                "scepter"          : 65,
                "blueMisfortune"   : 66,
                "redMisfortune"    : 67,
                "greenMisfortune"  : 68,
            };
            this.exploitTypes      = {};
            this.colors            = {'D56F12':'orange', '000000' : 'black', 'B6B525' : 'green', '5D8688' : 'blue'} ;
            this.currentCost       = 0;
            this.connexions        = {};
            this.pileList          = ['pile1', 'pile2', 'pile3'];
            this.playedAction      = "";
            this.clientStateArgs   = {};
            this.parentSide        = [];
            this.diceSelectionArgs = {};
            this.lastSideUp        = {}; // { player_id : [ { id: side_id, type: side_type }, (...) ], (...) }
            this.triple            = 1;
            this.turnPlayerId      = 0;
            this.translatableTexts = {};
            this.currentFilter     = '';
            this.sidesInit         = {};
            this.initPools         = {};
            this.myDlg             = '';

            // https://codeburst.io/the-only-way-to-detect-touch-with-javascript-7791a3346685
            window.addEventListener('touchstart', function() {
                self.isTouchScreen = true;
            });

            // window.addEventListener('resize', function() {
            // });
        },

        /*
            setup:

            This method must set up the game user interface according to current game situation specified
            in parameters.

            The method is called each time the game interface is displayed to a player, ie:
            _ when the game starts
            _ when a player refreshes the game page (F5)

            "gamedatas" argument contains all datas retrieved by your "getAllDatas" PHP method.
        */

        setup: function( gamedatas )
        {
            console.log( "Starting game setup" );
            console.log(gamedatas);

            var self = this;

            // this.connexions['debugResources'] = dojo.connect(document.getElementById('debug_resources'), 'onclick', this, 'debugResourcesAll');

            // don't put this texts in init, as DivYou would not be inited
            this.translatableTexts = {
                'question'                      : _('?'),
                'round'                         : _('Round'),
                'yes'                           : _('Yes'),
                'no'                            : _('No'),
                'cancel'                        : _('Cancel'),
                'pass'                          : _('Pass'),
                'confirm'                       : _('Confirm'),
                'endForgeButton'                : _('End Forge'),
                'endTurnButton'                 : _('End Turn'),
                'tooltipCardCost'               : _("Cost: "),
                'tooltipCardVP'                 : _("VP: "),
                'shipMultipleActionChoice'      : _("Choose what to do first: "),
                'actionUseCerberusToken'        : _("Use your Cerberus token?"),
                'helpActionMessage'             : _('Click on an action button first (top of the screen)'),
                'mirrorDialogTitle'             : _("Choose ${nb} side(s) to replace the mirror(s)"),
                'silverHindDialogTitle'         : _('Select a die (Silver Hind)'),
                'oracleHindDialogTitle'         : _('Select a die (Oracle)'),
                'draftNoCardSelectedError'      : _("You must select a card first"),
                'draftTooManyCardSelectedError' : _("Only one card can be selected"),
                'buyExploitConfirmation'        : _('Please confirm you want to buy this card ?'),
                'passReinforcementConfirmation' : _('You did not use all your reinforcement(s), are you sure you want to pass ?'),
                'forgeDescriptionMyTurn'        : this.divYou() + " " + _('must choose the side you want THEN where you want to forge it'),
                'isForgingDescriptionMyTurn'    : this.divYou() + " " + _("may keep on forging your dices"),
                'exploitDescriptionMyTurn'      : this.divYou() + " " + _('must choose an heroic feat'),
                'tritonTokenDescriptionMyTurn'  : this.divYou() + " " + _('choose resources for the Token'),
                'owlDescriptionMyTurn'          : this.divYou() + " " + _('must select a resource: '),
                'satyrsDescriptionMyTurn'       : this.divYou() + " " + _('must select 2 sides from opponents'),
                'minotaurDescriptionMyTurn'     : this.divYou() + " " + _('must choose resources to loose (Minotaur effect)'),
                'sphinxDescriptionMyTurn'       : this.divYou() + " " + _('must select a die'),
                'discardedSidesTooltipTitle'    : _('Discarded sides'),
                'secondActionTooltipTitle'      : _('Second action taken'),
                'firstPlayerTooltipTitle'       : _('First player'),
                'lastTurnMessage'               : _('This is the last turn'),
                'rollsLogButton'                : _('${playerName} rolls log'), // to translate later
                'rollsLogsTextSearch'           : _('${logPlayerName} rolls'),
                'autoHammerEnableButton'        : this.replaceTextWithIcons( _('Enable Auto [H]') ),
                'autoHammerDisableButton'       : this.replaceTextWithIcons( _('Disable Auto [H]') ),
                'scepterTokenDescriptionMyTurn' : _("Select the ressource to get"),
                'buyWithAncientShardDescriptionMyTurn' : _("Select how to accomplish the exploit"),
                "cancelScepters"                : _("Reset scepter positions"),
                "convertCompanion"              : _("Do you confirm the conversion of the Companion?"),
                "merchantFirstStep"             : _("Choose the number of upgrade that you wish to do:"),
                "merchantSecondStep"            : _("Choose the side on your die to replace THEN the new one"),
                "upgradeNotPossible"            : _("This side cannot be upgraded as it has reached maximum level"),
                "merchantTooMuchUpgrade"        : _("Please select less upgrade as it exceeds upgrade limit"),
                "celestialMirror"               : _("Celestial Mirror: select a side to gain its resources"),
                "celestialChooseSide"           : _("Select a side to put it face up and gain its resources"),
                "reroll"                        : _("Reroll (-3[G])"),
                "confirmEndTurn"                : _("Do you wish to end your turn?"),
                "mazeClassicalForge"            : _("[mForge] You may forge a side"),
                "mazeChoosePath"                : _("Choose your path "),
                "mazeChooseTreasure"            : _("Choose the treasure "),
                "mazeConfirmReward"             : _("Confirm the reward "),
                "celestialChooseSides"          : _("Select a side per die to put it face up and gain its resources"),
                "seeDiscardTooltip"             : _("Click here to see your discard, then click on the panel that opened to hide it"),
                "ancestorNoSide"                : _("Select a die to gain a minor blessing"),
                "showChooseDialog"              : _("Show mirror dialog"),
                "forgeOriginalSide"             : _("Do you confirm the replacement of an updagred side?"),
                "notEnoughResource"             : _("Not enough resources"),
                "memoryIsland"                  : _("Select an island on which to put the reward on it"),
                'fortuneWheel'                  : _("Select one side of each die as a prediction for the Wheel of Fortune"),
            }

            this.exploitTypes = gamedatas.exploitTypes;
            this.sidesInit    = gamedatas.sides_init;
            this.initPools    = gamedatas.initPools;

            var nbPlayers = Object.keys(gamedatas.players).length;
            if ( nbPlayers == 2 )
                dojo.addClass('game_play_area', 'two-players');
            else if ( nbPlayers == 3 )
                dojo.addClass('game_play_area', 'three-players');
            else if ( nbPlayers == 4 )
                dojo.addClass('game_play_area', 'four-players');

            // document.getElementById("lastTurn").innerHTML = this.translatableTexts.lastTurnMessage;

            // TODO: Comment for PROD
            dojo.addClass( 'loader_mask', 'hide' );

            var celestial = [
                {'id':1111, 'type':'G12', 'class':this.sideClass['G12'] },
                {'id':1112, 'type':'V5', 'class':this.sideClass['V5'] },
                {'id':1131, 'type':'V3G3orFS1orMS1', 'class':this.sideClass['V3G3orFS1orMS1'] },
                {'id':1114, 'type':'celestialMirror', 'class':this.sideClass['celestialMirror'] },
                {'id':1115, 'type':'chooseSide', 'class':this.sideClass['chooseSide'] },
                {'id':1161, 'type':'doubleUpgrade', 'class':this.sideClass['doubleUpgrade'] },
            ];

            //dojo.place(this.format_block('jstpl_celestial_dice', celestial), 'turn-container');
            dojo.place(this.format_block('jstpl_celestial_dice', celestial), 'pool-1', 'before');

            // Setting up player boards
            for( var player_id in gamedatas.players )
            {
                // console.log ('player id ' + player_id);
                var player = gamedatas.players[player_id];

                var player_panel     = $('player_board_' + player_id);
                var player_play_area = $('player-container-' + player_id);

                // Turn order in panels
                dojo.place( this.format_block('jstpl_turn_order', {'order':gamedatas.turnOrder[ player_id ]}), player_panel );

                // Button Auto Hammer in self panel
                if ( player_id == this.player_id ) {
                    var hammer_auto = gamedatas.players[ player_id ].hammer_auto;
                    var text = hammer_auto == 1 ? this.translatableTexts.autoHammerDisableButton : this.translatableTexts.autoHammerEnableButton;

                    var btnAutoHammer = dojo.place( this.format_block('jstpl_bga_btn', {
                        'color'   : 'gray',
                        'classes' : 'btn-auto-hammer',
                        'id'      : 'btn-auto-hammer',
                        'text'    : text,
                    } ), player_panel );

                    dojo.attr( btnAutoHammer, 'data-enabled', gamedatas.players[ player_id ].hammer_auto );
                    dojo.connect( btnAutoHammer , 'onclick', this, 'onClickAutoHammer' );

                    $seeDiscard = document.getElementById('see-discard');
                    dojo.connect( $seeDiscard , 'onclick', this, 'onClickShowDiscard' );

                    this.addTooltipHtml( $seeDiscard.id, this.format_block('jstpl_tooltip_classic', {
                        'title' : this.translatableTexts.seeDiscardTooltip
                    } ));

                    $discardOverlay = document.querySelector('.fixed-center');
                    dojo.connect( $discardOverlay , 'onclick', this, 'onClickHideDiscard' );


                    var btnShowChooser = dojo.place( this.format_block('jstpl_bga_btn', {
                        'color'   : 'blue',
                        'classes' : 'btn-auto-hammer',
                        'id'      : 'btn-show-chooser',
                        'text'    : this.translatableTexts.showChooseDialog,
                    } ), player_panel );

                    dojo.connect( btnShowChooser , 'onclick', this, 'onClickShowDialog' );
                    dojo.addClass(btnShowChooser, 'hide');
                }

                // EXPERIMENTAL - buttons to filter log - for salty players
                var filterButton = dojo.place( this.format_block('jstpl_bga_btn', {
                    'color'   : 'gray',
                    'classes' : 'btn-filter-log hide',
                    'id'      : 'btn-filter-log-' + player.name,
                    'text'    : this.translatableTexts.rollsLogButton.replace('${playerName}', player.name),
                }), player_panel, 'last' );
                dojo.connect( filterButton , 'onclick', this, 'onClickFilterRolls' );

                // Discarded sides
                this.addTooltipHtml( 'container_discarded_sides_p' + player_id, this.format_block('jstpl_tooltip_classic', {
                    'title' : this.translatableTexts.discardedSidesTooltipTitle
                } ));

                // Ressources container
                dojo.place(this.format_block('jstpl_player_ressource', player), 'first-flex-container-' + player_id, 'first');

                this.addTooltipHtml( 'action_p' + player_id, this.format_block('jstpl_tooltip_classic', {
                    'title' : this.translatableTexts.secondActionTooltipTitle
                } ));


                // Ancient shards if mod activated
                if (this.gamedatas.counters['ancientshardcount_p' + player_id]) {
                    var data = {
                        "id": player_id,
                    };

                    dojo.place(this.format_block('jstpl_ancient_shard', data), 'ressources_container_p' + player_id, 'last');
                }

                dojo.place(this.format_block('jstpl_hammer', player), 'ressources_container_p' + player_id, 'last');

                // Hammer token if card is owned
                if (this.gamedatas.counters['hammercount_p' + player_id]) {
                    dojo.query('#hammercount_p' + player_id)[0].innerHTML = this.gamedatas.counters[ 'hammercount_p' + player_id ];
                    dojo.query('#hammers_p' + player_id)[0].innerHTML = player.nbHammer;
                    dojo.removeClass('hammer_container_p' + player_id, 'hide');

                    if (~~(this.gamedatas.players[player_id]['hammer_position']/15)&1)
                        // phase 2
                        dojo.addClass('hammer_p' + player_id, 'ressources-hammer2');
                    else
                        // phase 1
                        dojo.addClass('hammer_p' + player_id, 'ressources-hammer1');

                    if (player.remainingHammer > 1)
                        dojo.removeClass('hammersleft_p' + player_id, 'hide');
                }

                merchant = 0;
                for(var card in this.gamedatas.exploits['pile3-'+player_id])
                {
                    if( this.gamedatas.exploitTypes[ this.gamedatas.exploits[ 'pile3-' + player_id ][card]['type'] ]['actionType'] == 'recurrent' )
                    {
                        var card_id   = this.gamedatas.exploits['pile3-' + player_id][card]['id'];
                        var card_type = this.gamedatas.exploits['pile3-' + player_id][card]['type'];

                        if (card_type == 'merchant') {
                            merchant++;

                            // do no place another token if there is one
                            if (merchant > 1) {
                                continue;
                            }
                        }

                        dojo.place( this.format_block('jstpl_power', {
                            'id'   : card_id,
                            'type' : card_type,
                        }), 'powers_p' + player_id );
                        this.addPowerToolTip( 'power-' + card_id, card_type );
                    }
                }

                if (merchant > 0) {
                    $merchant = document.querySelector('#powers_p' + player_id + ' .power-merchant');
                    $merchant.innerHTML = merchant
                }

                // Display of triton & cerberus tokens on the player panel
                for (i=0; i< player['triton']; i++)
                {
                    dojo.place( this.format_block('jstpl_token_id', {
                        'size'      : 'small',
                        'type'      : 'triton',
                        'player_id' : player_id,
                        'num'       : i,
                    }), 'tokens_p' + player_id );
                    this.addPowerToolTip('token-triton-' + player_id + '-' + i, 'triton');
                }

                for (i=0; i< player['cerberus']; i++)
                {
                    dojo.place( this.format_block('jstpl_token_id', {
                        'size'      : 'small',
                        'type'      : 'cerberus',
                        'player_id' : player_id,
                        'num'       : i,
                    }), 'tokens_p' + player_id );
                    this.addPowerToolTip('token-cerberus-' + i, 'cerberus');
                }

                // First player token
                if (firstPlayerID == player_id)
                {
                    dojo.place(this.format_block('jstpl_ressource_id', {'id':'first-player-token','size':'small', 'type':'first'}), 'action_p' + player_id, 'before');

                    this.addTooltipHtml( 'first-player-token', this.format_block('jstpl_tooltip_classic', {
                        'title' : this.translatableTexts.firstPlayerTooltipTitle
                    } ));
                }

                // Pawn position
                if(player['position']== 'begin')
                    dojo.place (this.format_block('jstpl_player_pawn', {'color': this.colors[player['color']]}), 'position-init-'+ this.colors[player['color']]);
                else
                    dojo.place (this.format_block('jstpl_player_pawn', {'color': this.colors[player['color']]}), 'position-'+ player['position']);

                // Init of dices
                this.lastSideUp[ player_id ] = {};
                var dices = gamedatas.playersDice[player_id];

                var dice1 = dices.dice1.map( function(value){
                    return {
                        "id"    : value.id,
                        "class" : self.sideClass[value.type],
                        "type"  : value.type,
                    };
                });

                dice1.dice      = 1;
                dice1.player_id = player_id;
                dice1.type      = 'fire';

                var dice2 = dices.dice2.map( function(value){
                    return {
                        "id"    : value.id,
                        "class" : self.sideClass[value.type],
                        "type"  : value.type,
                    };
                });

                dice2.dice      = 2;
                dice2.player_id = player_id;
                dice2.type      = 'moon';

                this.lastSideUp[ player_id ][ 1 ] = {
                    "id"   : dices.dice1[0].id,
                    "type" : dices.dice1[0].type
                };
                this.lastSideUp[ player_id ][ 2 ] = {
                    "id"   : dices.dice2[0].id,
                    "type" : dices.dice2[0].type
                };

                dojo.place(this.format_block('jstpl_dice', dice1), 'player-'+ player_id + '-dice-3D-1');
                dojo.place(this.format_block('jstpl_dice', dice2), 'player-'+ player_id + '-dice-3D-2');

                dojo.place(this.format_block('jstpl_dice_flat', dice1), 'player-'+ player_id + '-dice-1');
                dojo.place(this.format_block('jstpl_dice_flat', dice2), 'player-'+ player_id + '-dice-2');
            }

            /**
             * Initialisation of the pools
             **/
            for (var poolId in this.gamedatas.sides) {
                this.pools[poolId] = new ebg.stock();
                this.pools[poolId].create (this, $('pool-' + poolId), 45, 45);
                this.pools[poolId].image_items_per_row = 100;
                this.pools[poolId].item_margin = 0;
                this.pools[poolId].setSelectionMode(0);
                this.pools[poolId].setSelectionAppearance('class');
                this.pools[poolId].onItemCreate = dojo.hitch( this, 'addSideStuff' );

                for (var sideIndex in this.gamedatas.sides[poolId]) {
                    var side = this.gamedatas.sides[poolId][sideIndex];
                    var sideType = side.type; // ex: G6, mirror, FS2...

                    // Position of the side in the sprite ressource
                    var sidePosition = this.sidePosition.indexOf(sideType);

                    this.pools[poolId].addItemType(sidePosition, 0, g_gamethemeurl, sidePosition);
                    this.pools[poolId].addToStockWithId(sidePosition, side['id'], 'pool-' + poolId);

                    if (this.gamedatas.dice_sides[sideType].hasOwnProperty('tooltip')) {
                        this.addTooltipHtml('pool-' + poolId + '_item_' + side['id'],
                            this.format_block('jstpl_tooltip_side', {'description' : this.replaceTextWithIcons(_(this.gamedatas.dice_sides[sideType]['tooltip'] ))})
                        );
                    }
                }
            }

            // Exploit init
            exploitId = 0;
            for (var slot_num in this.exploitSlot) {
                var slot = this.exploitSlot[slot_num];
                this.exploits[slot] = new ebg.stock();
                this.exploits[slot].create(this, $('exploit-' + slot) , this.exploitWidth, this.exploitHeight);
                this.exploits[slot].image_items_per_row = this.exploitByRow;
                this.exploits[slot].setOverlap(0.25,0.25);
                this.exploits[slot].setSelectionMode(0);
                this.exploits[slot].item_margin = 0;
                this.exploits[slot].onItemCreate = dojo.hitch( this, 'addCardStuff' );

                if (this.gamedatas.exploits.hasOwnProperty(slot)) {
                    var nb_exploits_left = Object.keys( this.gamedatas.exploits[slot] ).length;
                    dojo.place(this.format_block('jstpl_card_number', {
                        'slot' : slot,
                        'nb'   : nb_exploits_left
                    }), 'exploit-' + slot, 'first');

                    for(var i = Object.keys(this.gamedatas.exploits[slot]).length - 1; i >= 0; i--) {
                        var position = Object.keys(this.gamedatas.exploits[slot])[i];
                        var card_exploit = this.gamedatas.exploits[slot][position];
                        this.exploits[slot].addItemType(slot, 0, g_gamethemeurl + 'img/sprite-cards-reb.jpg', this.exploitSpritePosition[card_exploit['type']] * 2);
                        this.exploits[slot].addToStockWithId(slot, card_exploit['id']);
                    }
                }
            }

            // exploit played
            for( var player_id in this.gamedatas.players ) {
                var playerPile = 'pile-' + player_id;
                this.exploits[playerPile] = new ebg.stock();
                this.exploits[playerPile].create(this, $(playerPile) , this.exploitWidth, this.exploitHeight);
                this.exploits[playerPile].image_items_per_row = this.exploitByRow;
                this.exploits[playerPile].setOverlap(0.01,0.01);
                this.exploits[playerPile].setSelectionMode(0);
                this.exploits[playerPile].item_margin = 0;
                this.exploits[playerPile].onItemCreate = dojo.hitch( this, 'addCardStuff' );

                for (var pile in this.pileList) {
                    exp = this.pileList[pile] + '-' + player_id;

                    // add the type for each card
                    for (var exp_card in this.gamedatas.exploitTypes) {
                        card_exploit = this.gamedatas.exploitTypes[exp_card];
                        this.exploits[playerPile].addItemType(exp_card, 0, g_gamethemeurl + 'img/sprite-cards-reb.jpg', this.exploitSpritePosition[exp_card] * 2 + 1);
                    }

                    // only for cards owned
                    if (this.exploitSlot.indexOf(exp) == -1 && exp.substr(0, 5) != 'table') {
                        for(var position in this.gamedatas.exploits[exp]) {
                            card_exploit = this.gamedatas.exploits[exp][position];
                            card_type = card_exploit.type;
                            if ( this.classEffect[ card_type ] != undefined ) {
                                var elId = 'token-' + player_id + '-' + card_exploit.id;
                                dojo.place(
                                    this.format_block('jstpl_ressource_id', {
                                        'id'   : elId,
                                        'size' : 'small',
                                        'type' : this.classEffect[ card_type ]
                                    }),
                                    'action_p' + player_id, 'before'
                                );

                                this.addPowerToolTip( elId, card_type );
                            }

                            this.exploits[playerPile].addToStockWithId(card_exploit['type'], card_exploit['id']);
                        }
                    }
                }

                // handle activated twins
                for(var cardId in this.gamedatas.exploits['pile2-'+player_id]) {
                    var card = this.gamedatas.exploits['pile2-'+player_id][cardId];

                    //console.log(card);
                    if (card.type == 'twins' && card.type_arg == '1') {
                        dojo.query('#token-' + player_id + '-' + cardId).addClass('unusable');
                    }
                }

                if ( gamedatas.exploits.hasOwnProperty( 'table-' + player_id ) ) {
                    for ( var index in gamedatas.exploits[ 'table-' + player_id ] )
                    {
                        var card = gamedatas.exploits[ 'table-' + player_id ][ index ];
                        this.exploits[ 'pile-' + player_id ].addToStockWithId(card['type'], card['id']);
                    }
                }

                if ( this.gamedatas.powerTokens.hasOwnProperty(player_id)) {
                    for (var power in this.gamedatas.powerTokens[player_id]) {
                        //console.log('tata', this.gamedatas.powerTokens[player_id][power].state);
                        var pow = power.split('_');
                        if (pow[0] != 'companion') {
                            // scepter
                            var $el = dojo.place( dojo.place( this.format_block('jstpl_token_scepter', {
                                'size'      : 'small',
                                'type'      : pow[0],
                                'player_id' : player_id,
                                'num'       : pow[1],
                            }), 'tokens_p' + player_id ),'tokens_p' + player_id);

                            $el.classList.add('token-' + pow[0] + '-' + this.gamedatas.powerTokens[player_id][power].state);
                        }
                        // Companion should not be added if used
                        else if (pow[0] == 'companion') {
                            this.notifCompanion({args : {card_id : 'power-' + pow[1], val : this.gamedatas.powerTokens[player_id][power].state}});

                        }
                        this.addPowerToolTip('token-' + pow[0] + '-' + player_id + '-' + pow[1], pow[0]);
                    }
                }
            }

            // exploit draft stock
            this.exploits['draft'] = new ebg.stock();
            this.exploits['draft'].create(this, $('draft-stock') , this.exploitWidth, this.exploitHeight);
            this.exploits['draft'].image_items_per_row = this.exploitByRow;
            this.exploits['draft'].setSelectionMode(0);
            this.exploits['draft'].item_margin = 5;
            this.exploits['draft'].onItemCreate = dojo.hitch( this, 'addCardStuff' );
            for (var type in this.gamedatas.exploitTypes) {
                cardObject = this.gamedatas.exploitTypes[type];
                this.exploits['draft'].addItemType(type, 0, g_gamethemeurl + 'img/sprite-cards-reb.jpg', this.exploitSpritePosition[type] * 2);
            }

            // handle turn order ()
            // var orderTurn = this.swapKeys( gamedatas.turnOrder );
            // var nbKeys = Object.keys(orderTurn).length;
            // for ( i = 1 ; i <= nbKeys ; i++ ) {
            //     var player_id = orderTurn[ i ];
            //     var newEl = dojo.clone( 'avatar_' + player_id );
            //     newEl.id = 'savatar_' + player_id;
            //     dojo.removeClass( newEl, 'avatar');
            //     dojo.addClass( newEl, 'turn-avatar ' + this.colors[ gamedatas.players[ player_id ].color ] );
            //     dojo.place(newEl, 'turn-order-container');
            // }

            dojo.place( this.format_block('jstpl_turn_count', {
                'title'     : this.translatableTexts.round,
                'nbTurns'   : this.gamedatas.nbTurns,
                'turnCount' : this.gamedatas.turnCount,
            }), 'nb-turns-container' );

            this.turnPlayerId = gamedatas.turnPlayerId;

            if (this.turnPlayerId != 0) {
                dojo.addClass( 'overall_player_board_' + gamedatas.turnPlayerId, 'active' );
                dojo.addClass( 'player-container-' + gamedatas.turnPlayerId, 'active' );
            }

            if ( gamedatas.secondActionTaken == 1 )
                dojo.removeClass( 'action_p' + gamedatas.turnPlayerId, 'hide' );

            if (gamedatas.remainingTurns == 1) {
                var el = document.getElementById('nb-turns-container');
                dojo.addClass(el, 'lastTurn');
                el.innerHTML = this.translatableTexts.lastTurnMessage;
            }

            // hide yourself div if specatator
            if (this.isSpectator) {
                dojo.addClass(dojo.byId('player-container-' + this.player_id), 'hide');
            }
            // Counters init
            this.updateCounters(gamedatas.counters);

            // Celestial dice tooltip
            for (i = 1 ; i <= 6 ; i++) {
                this.addTooltipHtml('celestial_side_container_' + i, this.format_block('jstpl_tooltip_title', {
                    'title'       : this.gamedatas.celestialInfo.name,
                    'description' : this.replaceTextWithIcons(_(this.gamedatas.celestialInfo.description)),
                }));
            }

            // if not rebellion, hide modules
            if (this.gamedatas.rebellion < 3) {
                dojo.addClass("rebellion-pools", "hide");
                dojo.addClass("maze-board", "hide");
                dojo.addClass("titan-board", "hide");
                // Hide celestial die
                if (!this.gamedatas.hasCelestial)
                    dojo.addClass("celestial_dice", "hide");
            }
            // Titan
            else if (this.gamedatas.rebellion == 3) {
                dojo.addClass("maze-board", "hide");
                // Hide celestial die
                if (!this.gamedatas.hasCelestial)
                    dojo.addClass("celestial_dice", "hide");


                for (var passiveKey in this.gamedatas.titanPassives) {
                    var passive = this.gamedatas.titanPassives[passiveKey];

                    $el = document.querySelector('[data-ref="' + passiveKey + '"]');

                    this.addTooltipHtml($el.id, this.format_block('jstpl_tooltip_side', {
                        'description' : this.ressourcesTextToIcon(_(passive.description)),
                    }));
                }

                for( var player_id in this.gamedatas.players ) {
                    var position = this.gamedatas.zones['position_' + player_id].state;

                    dojo.place(this.format_block('jstpl_titan_player', {
                        'color' : this.colors[this.gamedatas.players[player_id]['color']],
                    }), 'titan-tile-' + position);

                    //var $anotherPlayer = this.format_block('jstpl_token', {
                    //    'size' : 'small',
                    //    'type' : this.colors[this.gamedatas.players[player_id]['color']] + '-player',
                    //});


                }

                for (var token in this.gamedatas.memoryTokens) {
                    if (this.gamedatas.memoryTokens[token].location == 'none' || this.gamedatas.memoryTokens[token].location == 'used' )
                        continue ;

                    var token_split = token.split('_');
                    var side = "";
                    var token_owner = "";
                    let ressource = "";
                    //console.debug(notif.args);

                    if (this.gamedatas.memoryTokens[token].state == '1') {
                        side = 'sun';
                        ressource = '2 [L] 1 [FS]';
                    }
                    else {
                        side = 'moon';
                        ressource = '2 [AS] 1 [MS]';
                    }

                    dojo.place(
                            this.format_block('jstpl_memory_id', {
                                'id'   :  token,
                                'type' : this.memoryMap[token_split[0]] + side,
                            }), 'memory-' + this.gamedatas.memoryTokens[token].location
                        );

                    token_owner = '<span style="font-weight:bold;color:#' + this.gamedatas.players[token_split[2]]['color'] + '">' + this.gamedatas.players[token_split[2]]['name'] + '</span>';

                    this.addTooltipHtml(token, this.format_block('jstpl_tooltip_title', {
                                'title': _('Memory token of ') + token_owner,
                                'description' : _('Gain ') + this.ressourcesTextToIcon(ressource)}));

                }

            }
            // Goddess
            else if (this.gamedatas.rebellion == 4) {
                dojo.addClass("titan-board", "hide");
                // Maze management
                for (i = 1; i <= 36; i++) {
                    if (this.gamedatas.maze[i].reward !== null && this.gamedatas.maze[i].description != '') {
                        this.addTooltipHtml( 'maze-tile-' + i, this.format_block('jstpl_tooltip_maze', {
                            'description' : this.ressourcesTextToIcon(_(this.gamedatas.maze[i].description)),
                            'icon'        : this.format_block('jstpl_maze_element', {
                                'size' : 'big',
                                'type' : this.mazeClass['m' + this.gamedatas.maze[i].reward ],
                            }),
                        } ));
                    }
                }

                this.addTooltipHtml( 'maze-first-finish', this.format_block('jstpl_tooltip_maze', {
                    'description' : this.ressourcesTextToIcon(_(this.gamedatas.maze['firstFinish'].description)),
                    'icon'        : this.format_block('jstpl_maze_element', {
                        'size' : 'big',
                        'type' : this.mazeClass['m' + this.gamedatas.maze['firstFinish'].reward ],
                    }),
                }));

                // setup golems and player info
                for( var player_id in this.gamedatas.players ) {
                    var position = this.gamedatas.zones['position_' + player_id].state;

                    dojo.place(this.format_block('jstpl_golem', {
                        'color' : this.colors[this.gamedatas.players[player_id]['color']],
                    }), 'maze-tile-' + position);

                    var $anotherGolem = this.format_block('jstpl_token', {
                        'size' : 'small',
                        'type' : this.colors[this.gamedatas.players[player_id]['color']] + '-golem',
                    });

                    dojo.place(this.format_block('jstpl_player_maze_info', {
                        'golem' : $anotherGolem,
                        'color' : this.colors[this.gamedatas.players[player_id]['color']],
                        'name' : this.gamedatas.players[player_id]['name'],
                    }), 'maze-caption');

                }

                // treasure init
                for (var treasure in this.gamedatas.treasures) {
                    if (this.gamedatas.treasures[treasure]['location'] != 'none') {
                        var reward = treasure.split('_');

                        this.addTreasureToolTip(this.gamedatas.treasures[treasure]['location'], reward[1]);
                    }
                }
            }



            //$mazeOpacityRange = document.getElementById('maze-opacity');
            //console.log('dfMazeOpacity', this.getMyCookie('dfMazeOpacity'));
            //var mazeOpacityValue = this.getMyCookie('dfMazeOpacity') == null ? 1 : this.getMyCookie('dfMazeOpacity');
            //console.log('mazeOpacityValue', mazeOpacityValue);
            //$mazeOpacityRange.value = mazeOpacityValue;
            //$mazeBoard.className = '';
            //$mazeBoard.classList.add('opacity-' + (mazeOpacityValue * 10));
            //this.connexions['mazeOpacity'] = dojo.connect($mazeOpacityRange, 'onchange', this, 'onChangeMazeOpacity');

            var $mazeBoard = document.getElementById('maze-board');
            var mazePulseValue = this.getMyCookie('dfMazePulse') == null ? 'true' : this.getMyCookie('dfMazePulse');

            if (mazePulseValue == 'true') {
                $mazeBoard.classList.add('pulse');
            }

            this.connexions['mazePulse'] = dojo.connect($mazeBoard, 'onclick', this, 'onClickMazePulse');

            if (this.gamedatas.hasOwnProperty('celestial'))
                this.rollCelestialDice(gamedatas.celestial, false);

            this.activateTritonToken();

            // scepter management
            let active_player_id = gamedatas.turnPlayerId;
            if (active_player_id != 0) {
                this.notifUseScepter({'args' :
                                        {
                                            'player_id' : active_player_id,
                                            'fireshard' : gamedatas.convertedScepter.fire,
                                            'moonshard': gamedatas.convertedScepter.moon
                                        }
                                    });
            }
            // Setup game notifications to handle (see "setupNotifications" method below)
            this.setupNotifications();
        },

        onClickShowDiscard: function(event)
        {
            //console.log('onClickShowDiscard', event);
            dojo.stopEvent(event);

            $overlay = document.querySelector('.fixed-center');
            $overlay.classList.toggle('show');

            $myDiscard = document.querySelector('.current-player-play-area .cards-pile');
            $overlay.innerHTML = $myDiscard.innerHTML;

            $overlay.querySelectorAll('.exploit').forEach(function($card) {
                $card.removeAttribute('id');
            });
            $overlay.querySelectorAll('.card-counter').forEach(function($card) {
                $card.removeAttribute('id');
            })
        },

        onClickHideDiscard: function(event)
        {
            //console.log('onClickHideDiscard', event);
            dojo.stopEvent(event);

            $overlay = document.querySelector('.fixed-center');
            $overlay.innerHTML = '';
            $overlay.classList.remove('show');
        },

        ///////////////////////////////////////////////////
        //// Game & client states

        // fState: this method is called each time we are entering into a new game state.
        //                  You can use this method to perform some user interface changes at this moment.
        //
        onEnteringState: function( stateName, args )
        {
            console.log('Entering state: ' + stateName, args );

            switch( stateName ) {
                case 'draft':
                    // hide players board
                    dojo.query(".container-play-area").addClass('hide');

                    // show draft block
                    dojo.query("#draft-container").removeClass('hide');

                    // add cards inside + tooltips + connexions
                    var slot = args.args.slot;
                    for (var index in args.args.exploits) {
                        var card_object = args.args.exploits[index];
                        this.exploits[ 'draft' ].addToStockWithId( card_object.type, card_object.type );
                    }

                    if( this.isCurrentPlayerActive() ) {
                        this.exploits['draft'].setSelectionMode(1);
                    }
                    break;
                case 'endPlayerTurn':
                    if ( el = dojo.query(".ressources-2nd-action:not(.hide)")[0] )
                        dojo.addClass( el, 'hide' );
                    break;
                case 'tritonToken':
                    this.prepareChoiceState(args, 'actUseTritonToken');
                    break;
                case 'exploitRessource':
                    this.playedAction = args.args.card.action;
                    if ((this.isCurrentPlayerActive() || args.active_player == this.player_id)
                        && args.args.hasOwnProperty(this.player_id)
                        && args.args[this.player_id].action == 'side'
                    ) {
                        console.log('Played action' + this.playedAction);

                        if (args.args[this.player_id].firstFinish) {
                            $('pagemaintitletext').innerHTML = this.translatableTexts.celestialChooseSides;
                            // Activate flat side to allow one selection on each dice
                            this.selfSides.activate('OneSidePerDice', 'onClickConfirmGoddessCard');
                        } else {
                            if (args.args.celestial != '' || args.args[this.player_id].side_choice.side98) {
                                console.log("non pas la");
                                this.prepareChoiceState({'args':args.args}, args.args[this.player_id].action);
                                break ;
                            }
                            switch (this.playedAction) {
                                case 'steal2':
                                    $('pagemaintitletext').innerHTML = this.translatableTexts.satyrsDescriptionMyTurn;

                                    var dices    = [];
                                    nbMirror = 0;

                                    this.clientStateArgs.side_choice = {'side1': true, 'side2': true};

                                    // GET ALL DICES/SIDES BUT MIRRORS
                                    for (var player_id in this.gamedatas.players) {
                                        if (player_id != this.player_id) {
                                            for (var i = 1 ; i <= 2 ; i++) {
                                                var is_mirror = this.lastSideUp[ player_id ][ i ].type == 'mirror' ? true : false;

                                                dices.push( {
                                                    "player_id" : player_id,
                                                    "dice"      : i,
                                                    "is_mirror" : is_mirror
                                                } );

                                                if ( is_mirror )
                                                    nbMirror++;
                                            }
                                        }
                                    }

                                    // if at least one enemy mirror, add self sides
                                    if ( nbMirror ) {
                                        for (var i = 1 ; i <= 2 ; i++) {
                                            var is_mirror = this.lastSideUp[ this.player_id ][ i ].type == 'mirror' ? true : false;

                                            dices.push( {
                                                "player_id" : this.player_id,
                                                "dice"      : i,
                                                "is_mirror" : is_mirror
                                            } );
                                        }
                                    }

                                    var args = {
                                        "title"           : this.translatableTexts.satyrsDescriptionMyTurn, // titre de la dialog
                                        "selectMode"      : 'sides',
                                        "nbToSelect"      : 2,
                                        "sameSelectable"  : ( nbMirror >= 2 ) ? true : false,
                                        "limitSelfSelect" : ( nbMirror == 1 ) ? true : false,
                                        "canCancel"       : false,
                                        "dices"           : dices,
                                        "action"          : 'onClickSideChoiceConfirm',
                                        "hideMirror"      : true
                                    };

                                    this.initDiceSelection( args );

                                    break;
                                case 'chooseSides':
                                    // only activate self sides if we should...
                                    // @vincent fixme plz
                                    if (args.args[this.player_id].mirror == 0) {
                                        // Activate flat side to allow one selection on each dice
                                        $('pagemaintitletext').innerHTML = this.translatableTexts.celestialChooseSides;
                                        this.selfSides.activate('OneSidePerDice', 'onClickConfirmGoddessCard');
                                    }

                                    break ;
                                case 'throwCelestialDie':
                                    //console.log(args.args.celestial);
                                    switch (args.args.celestial) {
                                        case 'celestialMirror':
                                            this.clientStateArgs.side_choice = {'side1': true, 'side2': false};

                                            var dices    = [];

                                            // GET ALL DICES/SIDES BUT MIRRORS
                                            for (var player_id in this.gamedatas.players) {
                                                for (var i = 1 ; i <= 2 ; i++) {
                                                    dices.push( {
                                                        "player_id" : player_id,
                                                        "dice"      : i,
                                                        "is_mirror" : this.lastSideUp[ player_id ][ i ].type == 'mirror' ? true : false,
                                                    } );
                                                }
                                            }

                                            var args = {
                                                "title"         : this.translatableTexts.celestialMirror,
                                                "selectMode"    : 'sides',
                                                "nbToSelect"    : 1,
                                                "mirrorVisible" : false,
                                                "dices"         : dices,
                                                "canCancel"     : 0,
                                                "action"        : 'onClickSideChoiceConfirm',
                                                "hideMirror"      : true
                                            };
                                            this.initDiceSelection( args );
                                            break ;
                                        case 'chooseSide':
                                            $('pagemaintitletext').innerHTML = this.translatableTexts.celestialChooseSide;
                                            this.selfSides.activate('OneSide', 'onClickConfirmCelestialMirror');
                                            break ;
                                        }
                                case 'fortuneWheel':
                                    $('pagemaintitletext').innerHTML = this.translatableTexts.fortuneWheel;
                                    // Activate flat side to allow one selection on each dice
                                    this.selfSides.activate('OneSidePerDice', 'onClickConfirmGoddessCard');
                                    break;

                            }
                        }
                    }
                    //if ((this.isCurrentPlayerActive() || args.active_player == this.player_id) && args.args.hasOwnProperty(this.player_id) && this.playedAction == 'looseThrow') {
                    //if ((this.isCurrentPlayerActive() || args.active_player == this.player_id) && this.playedAction == 'looseThrow') {
                    // Bug #13038 & 10356
                    if (this.isCurrentPlayerActive()
                        && this.playedAction == 'looseThrow'
                        && (args.args.hasOwnProperty(this.player_id)
                        && args.args[this.player_id].action != 'cerberusToken'
                        && (!args.args[this.player_id].sides.hasOwnProperty('0') || parseInt(args.args[this.player_id].sides[0].num) <= 2))
                    ) {
                        $('pagemaintitletext').innerHTML = this.translatableTexts.minotaurDescriptionMyTurn;
                    }
                    break;
                case 'reinforcement':
                    if( this.isCurrentPlayerActive() ) {
                        // stock server states args if user cancel
                        // BEWARE, override old statesInfo when new data comes from server
                        if ( this.statesInfo[ stateName ] == undefined || args != this.statesInfo[ stateName ] )
                            this.statesInfo[ stateName ] = this.duplicateObject(args);
                    }

                    this.activateReinforcement( args.args );
                    break;
                case 'playerAction':
                    if( this.isCurrentPlayerActive() )
                    {
                        // with action buttons
                        if (this.prefs[100].value == 1) {

                            if ( this.statesInfo[ stateName ] == undefined )
                                this.statesInfo[ stateName ] = this.duplicateObject(args);

                            if ( args.args.isForging ) {
                                args.args.descriptionmyturn = this.translatableTexts.isForgingDescriptionMyTurn;
                                this.gotoChooseForge( args.args );
                            }
                            else
                                this.enablePlayerActionHelp();
                        }
                        else if (this.prefs[100].value == 2) {
                            if ( args.args.isForging ) {
                                $('pagemaintitletext').innerHTML = this.translatableTexts.isForgingDescriptionMyTurn;
                                this.selectForge.init( {
                                    'isForging' : true
                                } );
                            }
                            else {
                                // console.log("coucoou");
                                this.selectForge.init();
                                this.activateExploits();
                                // console.log("coucoou22");
                            }
                        }
                        this.activateSpecificCards(args.args.companion);
                    }
                    break;
                case 'chooseForge':
                    // This is a client state so no need to check if it's active player
                    var isForging = args.args.hasOwnProperty('isForging') && args.args.isForging ? true : false;
                    this.selectForge.init( {
                        'isForging' : isForging
                    } );

                    break;
                case 'chooseExploit':
                    // This is a client state so no need to check if it's active player
                    if (args.slots)
                        this.activateExploits(args.slots);
                    else
                        this.activateExploits(null);
                    if (args.free) {
                        this.clientStateArgs.free = true;
                        this.removeActionButtons();
                    }

                    break;
                case 'exploitEffect':
                    if ( this.statesInfo[ stateName ] == undefined )
                        this.statesInfo[ stateName ] = this.duplicateObject(args);

                    if (args.args.hasOwnProperty('card')) {
                        this.playedAction = args.args.card.action;
                        // display of specific description only if the effect is not running
                        if (!args.args.effectRunning) {
                            //alert('--' + args.args.info.power_you+'--');
                            $('pagemaintitletext').innerHTML = args.args.info.player + " " + this.replaceTextWithIcons(_(args.args.info.power_desc));
                            if (this.isCurrentPlayerActive() || args.active_player == this.player_id)
                                $('pagemaintitletext').innerHTML = this.divYou() + " " + this.replaceTextWithIcons(_(args.args.info.power_you));
                        }
                        //console.log(args.args.celestial);
                        //if (args.args.celestial != '') {
                        //    console.log("boug");
                        //    break ;
                        //}
                        switch (this.playedAction) {
                            case '4Throws':
                            case '4ThrowsTransform':
                                if ( ( this.isCurrentPlayerActive() || args.active_player == this.player_id ) && !args.args.effectRunning ) {

                                    $('pagemaintitletext').innerHTML = this.translatableTexts.sphinxDescriptionMyTurn;

                                    var args = {
                                        "title"      : this.translatableTexts.sphinxDescriptionMyTurn, // titre de la dialog
                                        "selectMode" : 'flat',
                                        "nbToSelect" : 1,
                                        "canCancel"  : 0,
                                        "action"     : 'onDiceSelection4Throws',
                                        "dices"      : [
                                            {
                                                "player_id" : this.player_id,
                                                "dice"      : 1
                                            },
                                            {
                                                "player_id" : this.player_id,
                                                "dice"      : 2
                                            }
                                        ],
                                        "hideMirror"      : true
                                    };

                                    this.initDiceSelection( args );
                                }
                                break;
                            case 'side3x' :

                                var idSide = dojo.query('#pool-15 .bside').length ? dojo.query('#pool-15 .bside')[0].id.match(/pool-15_item_([0-9]+)/)[1] : 0;
                                params = {'sideToForge': idSide, 'forgeType' : 'triple'};

                                if ( this.isCurrentPlayerActive() && idSide )
                                    this.selectForge.init(params);

                                break;
                            case 'sideShip' :
                                var idSide = dojo.query('#pool-13 .bside').length ? dojo.query('#pool-13 .bside')[0].id.match(/pool-13_item_([0-9]+)/)[1] : 0;
                                params = {'sideToForge': idSide, 'forgeType' : 'ship'};

                                if ( this.isCurrentPlayerActive() && idSide )
                                    this.selectForge.init(params);

                                break;
                            case 'shieldForge' :
                                params = {'forgeType' : 'shield'};

                                if( this.isCurrentPlayerActive() )
                                    this.selectForge.init(params);

                                break;
                            case 'sideMirror':
                                var idSide = dojo.query('#pool-11 .bside').length ? dojo.query('#pool-11 .bside')[0].id.match(/pool-11_item_([0-9]+)/)[1] : 0;
                                params = {'sideToForge': idSide, 'forgeType' : 'mirror'};

                                if ( this.isCurrentPlayerActive() && idSide )
                                    this.selectForge.init(params);

                                break;
                            case 'boarForge':
                            case 'sideMisfortune':
                                if (this.isCurrentPlayerActive()) {
                                    for( var player_id in this.gamedatas.players ) {
                                        if (player_id != this.player_id)
                                            this.addActionButton( 'boar_player_' + player_id, '<span style="font-weight:bold;color:#' + this.gamedatas.players[player_id]['color'] + '">' + this.gamedatas.players[player_id]['name'] + '</span>', 'onClickBoarPlayer', null, null, 'gray');
                                    }
                                }

                                break;
                            case 'forge4G':
                                params = {'forgeType' : 'gold'};

                                if( this.isCurrentPlayerActive() )
                                    this.selectForge.init(params);
                                break ;
                            case 'forgeVP':
                                // choose of side
                                if (args.args.poolToForge != null && args.args.poolToForge != -1) {
                                    param = {'forgeType' : 'select', 'poolList' : [args.args.poolToForge]};
                                    if( this.isCurrentPlayerActive() )
                                        this.selectForge.init(param);
                                }
                                // no available side, choose of die
                                else if( this.isCurrentPlayerActive() && args.args.poolToForge == null){
                                   var args = {
                                        "title"      : this.translatableTexts.ancestorNoSide,
                                        "selectMode" : 'flat',
                                        "nbToSelect" : 1,
                                        "dices"      : [
                                            {
                                                "player_id" : this.player_id,
                                                "dice"      : 1
                                            },
                                            {
                                                "player_id" : this.player_id,
                                                "dice"      : 2
                                            }
                                        ],
                                        "action"     : 'onDiceSelectionAncestor'
                                     };

                                    this.initDiceSelection( args );
                                }
                                break ;
                            case 'forgeEverywhere':
                                params = {'forgeType' : 'select', 'poolList' : args.args.poolList};
                                if( this.isCurrentPlayerActive() )
                                    this.selectForge.init(params);
                                break ;
                            case 'freeExploit':
                                this.gotoChooseExploit({slots: ["M1", "M2", "F1", "F2"], free: true});
                                //this.setClientState("chooseExploit", {slots: ["M1", "M2", "F1", "F2"]});
                                break ;
                            case 'throwCelestialDie':
                                // should never happen
                                if (args.args.celestial != 'doubleUpgrade') {
                                    //this.restoreServerGameState();
                                    return ;
                                }

                                this.clientStateArgs.merchantNbUpgrade = 2;
                                this.clientStateArgs.callback = 'onClickCelestialUpgrade';
                                this.clientStateArgs.canCancel = false;
                                this.clientStateArgs.celestCancel = true;
                                this.setClientState("merchantSecondStep", {
                                    descriptionmyturn : this.translatableTexts.merchantSecondStep
                                });

                                break ;
                            case 'sideMoonGolem' :
                                var idSide = dojo.query('#pool-19 .bside').length ? dojo.query('#pool-19 .bside')[0].id.match(/pool-19_item_([0-9]+)/)[1] : 0;
                                params = {'sideToForge': idSide, 'forgeType' : 'moonGolem'};

                                if ( this.isCurrentPlayerActive() && idSide )
                                    this.selectForge.init(params);

                                break;
                            case 'sideSunGolem' :
                                var idSide = dojo.query('#pool-17 .bside').length ? dojo.query('#pool-17 .bside')[0].id.match(/pool-17_item_([0-9]+)/)[1] : 0;
                                params = {'sideToForge': idSide, 'forgeType' : 'sunGolem'};

                                if ( this.isCurrentPlayerActive() && idSide )
                                    this.selectForge.init(params);

                                break;
                            case 'sideDogged': // forge a dogged side
                                params = {'forgeType' : 'dogged'};

                                if( this.isCurrentPlayerActive() )
                                    this.selectForge.init(params);

                                break ;
                            case 'sideShieldRebellion':
                                 params = {'forgeType' : 'shieldRebellion'};

                                if( this.isCurrentPlayerActive() )
                                    this.selectForge.init(params);
                                break ;
                            case 'memoryTokens':
                                if (Object.keys(args.args.memory).length == 2) {
                                    $('pagemaintitletext').innerHTML = $('pagemaintitletext').innerHTML + " 1/2";
                                }
                                else
                                    $('pagemaintitletext').innerHTML = $('pagemaintitletext').innerHTML + " 2/2";
                                break ;
                            case 'wheelFortune':

                                break ;
                        }
                    }
                    break;
                case "exploitForgeBoar":
                    params = {'sideToForge' : args.args.id, 'forgeType' : args.args.type};

                    if( this.isCurrentPlayerActive() ) {
                        this.selectForge.init(params);
                    }
                    break;
                case 'chooseRessource':
                    this.prepareRessourceSelector();
                    break;
                case 'endScoring':
                    this.prepareEndGame();
                    break;
                case 'gameEnd':
                    this.prepareEndGame();
                    // remove all cards from stock => final container
                    for ( var player_id in this.gamedatas.players ) {
                        var stockFrom = "pile-" + player_id;
                        var items = this.exploits[ stockFrom ].getAllItems();
                        for (var index in items) {
                            item = items[index];
                            var nom_container = 'final-card-p' + player_id;
                            this.exploits[ nom_container ].addToStockWithId( item.type, item.id, stockFrom + "_item_" + item.id );
                            this.exploits[ stockFrom ].removeFromStockById(item.id);
                        }

                    }
                    break;
                case 'secondAction':
                    //this.playedAction = "";
                    $('pagemaintitletext').innerHTML = this.ressourcesTextToIcon($('pagemaintitletext').innerHTML);
                    this.activateSpecificCards(args.args.companion);
                    break;
                case 'forgeShip':
                case 'oustedForgeShip':
                case 'doeForgeShip':
                case 'exploitForgeShip':
                    // This is a client state so no need to check if it's active player
                    params = {};

                    if (this.isCurrentPlayerActive()) {

                        if (args.args.ship == 'doubleUpgrade') {
                            this.clientStateArgs.merchantNbUpgrade = 2;
                            this.clientStateArgs.callback = 'onClickCelestialUpgrade';
                            this.clientStateArgs.canCancel = false;
                            this.clientStateArgs.celestCancel = true;
                            this.setClientState("merchantSecondStep", {
                                descriptionmyturn : this.translatableTexts.merchantSecondStep
                            });
                        }
                        else
                            this.selectForge.init(params);
                    }
                    break;
                case 'scepterUse':
                    break ;
                case 'merchantSecondStep':
                    // console.log('selectForge.activateSelfSides');
                    var self = this;
                    dojo.query(".current-player-play-area .dice-flat .bside").addClass("clickable");
                    self.connexions['forge'] = dojo.query(".current-player-play-area .bside").map(function(el) {
                        return dojo.connect(el, "onclick", self, 'onClickMerchantThirdStep' );
                    });
                    break ;
                case 'memoryIsland':
                    var self = this;
                    dojo.query(".position:not(#position-init-blue):not(#position-init-green):not(#position-init-black):not(#position-init-orange)").addClass("clickable");
                    self.connexions['islands'] = dojo.query(".position:not(#position-init-blue):not(#position-init-green):not(#position-init-black):not(#position-init-orange)").map(function(el) {
                        return dojo.connect(el, "onclick", self, 'onClickMemorySetup' );
                    });
                    break ;
            }
        },

        // onLeavingState: this method is called each time we are leaving a game state.
        //                 You can use this method to perform some user interface changes at this moment.
        //
        onLeavingState: function( stateName )
        {
            switch( stateName )
            {
                case 'draft':
                    // empty stock
                    this.exploits['draft'].removeAll()
                    this.exploits['draft'].setSelectionMode(0);

                    break;
                case 'reinforcement':
                    this.deactivateReinforcement();
                    this.statesInfo = {};
                    break;
                case 'playerAction':
                    this.disablePlayerActionHelp();
                    this.deactivateReinforcement();

                    if (this.prefs[100].value == 2) {
                        this.selectForge.end();
                        this.deactivateExploits();
                    }

                    break;
                case 'secondAction':
                    this.deactivateReinforcement();
                    break ;
                case 'chooseForge':
                case 'forgeShip':
                    this.selectForge.end();
                    break;
                case 'chooseExploit':
                    this.deactivateExploits();
                    //this.cleanClientStateArgs();
                    break;
                case 'forgeDice':
                    this.hideSidesToForge();
                    dojo.query(".dices-container").removeClass("flat");
                    dojo.query(".forge").addClass("hidden");

                    break;
                case 'exploitEffect':
                    if ( this.connexions.hasOwnProperty("die1") ) {
                        dojo.disconnect( this.connexions["die1"] );
                        delete this.connexions["die1"];
                    }
                    if ( this.connexions.hasOwnProperty("die2") ) {
                        dojo.disconnect( this.connexions["die2"] );
                        delete this.connexions["die2"];
                    }

                    this.selectForge.end();
                    dojo.query(".dices-container").removeClass("flat");

                    break;
                case 'exploitRessource':
                case 'ressourceChoice':
                case 'playerOustingChoice':
                case 'doeRessourceChoice':
                case 'misfortuneChoice':
                case 'tritonToken':
                    this.cleanClientStateArgs();
                    this.statesInfo = {};
                    break;
                case 'dummmy':
                    break;
                case 'merchantSecondStep':
                    dojo.query(".current-player-play-area .bside").removeClass("selected");
                    dojo.query(".current-player-play-area .bside").removeClass("clickable");
                    if ( this.connexions.hasOwnProperty("forge") ) {
                        dojo.forEach(this.connexions["forge"], function(el) {
                            dojo.disconnect(el);
                        });
                        delete this.connexions["forge"];
                    }
                    this.selectForge.deactivatePoolSides();
                    break ;
                 case 'memoryIsland':
                    dojo.query(".position").removeClass("clickable");
                     if ( this.connexions.hasOwnProperty("islands") ) {
                        dojo.forEach(this.connexions["islands"], function(el) {
                            dojo.disconnect(el);
                        });
                        delete this.connexions["islands"];
                    }
                    break ;
            }
        },

        // onUpdateActionButtons: in this method you can manage "action buttons" that are displayed in the
        //                        action status bar (ie: the HTML links in the status bar).
        //
        onUpdateActionButtons: function( stateName, args )
        {
            console.log('onUpdateActionButtons: ' + stateName, args);

            $('pagemaintitletext').innerHTML = this.replaceTextWithIcons( $('pagemaintitletext').innerHTML );

            // Fail fast
            if( ! this.isCurrentPlayerActive() ) {
                return;
            }

            switch( stateName ) {
                case 'draft':
                    this.addActionButton('confirm_draft_button', this.translatableTexts.confirm, 'onClickDraftButton');
                    break;
                case 'reinforcement':
                    this.addActionButton('pass_reinforcement_button', this.translatableTexts.pass, 'onClickReinforcementPrePass');
                    break;

                case 'playerAction':
                    if (this.prefs[100].value == 1) {
                        this.addActionButton('player_action_button_forge', _('Forge'), 'onClickPlayerAction');
                        this.addActionButton('player_action_button_exploit', _('Heroic Feat'), 'onClickPlayerAction');
                        this.addActionButton( 'player_action_button_end', this.translatableTexts.endTurnButton, 'onClickPlayerAction', null, null, 'red' );
                    }
                    else if (this.prefs[100].value == 2) {
                        if ( args.isForging )
                            this.addActionButton( 'forge_action_button_end', this.translatableTexts.endForgeButton, 'onClickEndForge', null, null, 'red' );
                        else
                            this.addActionButton( 'player_action_button_end', this.translatableTexts.endTurnButton, 'onClickPlayerAction', null, null, 'red' );
                    }
                    if (args.scepters != 0)
                        this.addActionButton( 'player_action_cancel_scepters', this.translatableTexts.cancelScepters, 'onClickCancelScepter', null, null, 'red' );
                    break;

                case 'forgeShip':
                case 'oustedForgeShip':
                case 'doeForgeShip':
                case 'exploitForgeShip':
                    if (args.ship == "mForge")
                        $('pagemaintitletext').innerHTML = this.replaceTextWithIcons( this.translatableTexts.mazeClassicalForge );

                    if (!args[this.player_id].hasOwnProperty('sides')) {
                        toUse = '123456';
                    }
                    else
                        toUse = args[this.player_id].sides[0].num;

                    this.addActionButton('forge_ship_pass_' + toUse, this.translatableTexts.pass, 'onClickForgeShipPass', null, null, 'red');
                    break;
                case 'merchantFirstStep':
                    //console.log("toto");
                    //console.debug(this.clientStateArgs);

                    // force unselection of all sides in the pool
                    for (var pool in this.pools) {
                        this.pools[ pool ].unselectAll();
                    }
                    var upg = _("upgrade(s)");
                    for (var i = 0; i <= this.clientStateArgs.merchantNbUpgrade; i++) {
                        j = this.clientStateArgs.merchantNbUpgrade - i;
                        if (i == 0)
                            merchantText = this.replaceTextWithIcons(parseInt(j) * 2 + "[VP]");
                        else if (j == 0)
                            merchantText = this.replaceTextWithIcons(i + " " + upg);
                        else
                            merchantText = this.replaceTextWithIcons(i + " " + upg + " " + parseInt(j) * 2 + "[VP]");

                        this.addActionButton( 'merchant' + i + '_' + i + '_' + j, merchantText, 'onClickMerchantStepOne', null, null, 'gray');
                    }

                    this.addActionButton('merchantCancel', this.translatableTexts.cancel, 'restoreServerGameState', null, null, 'red');
                    break ;
                case 'merchantSecondStep':
                    if (!this.clientStateArgs.hasOwnProperty('canCancel') || this.clientStateArgs.canCancel)
                        this.addActionButton('merchantCancel', this.translatableTexts.cancel, 'restoreServerGameState', null, null, 'red');
                    else if (this.clientStateArgs.hasOwnProperty('celestCancel') && this.clientStateArgs.celestCancel === true)
                        this.addActionButton('merchantCancel', this.translatableTexts.cancel, 'onClickCancelCelestial', null, null, 'red');
                    break ;
                case 'chooseForge':
                    if ( args.isForging )
                        this.addActionButton('forge_action_button_end', this.translatableTexts.endForgeButton, 'onClickEndForge', null, null, 'red');
                    else
                        this.addActionButton('forge_action_button_cancel', this.translatableTexts.cancel, 'onClickCancelForge', null, null, 'red');
                    break;

                case 'chooseExploit':
                    this.addActionButton('exploit_action_button_cancel', this.translatableTexts.cancel, 'onClickCancelPlayerAction', null, null, 'red');
                    break;

                case 'secondAction':
                    var ressources = this.getPlayerRessources();

                    var validCombinations = [];
                    var fireCost = 2;

                    let combinations = [];
                    for(var i = 0; i <= fireCost; i++){
                        let text = i + ' [FS] ' + (fireCost - i) + ' [AS]';
                        combinations.push([i, 0, fireCost - i, text]);
                    }
                    validCombinations = combinations.filter(c => c[0] <= ressources.fireshard && c[2] <= ressources.ancientshard);

                    validCombinations = this.generateButtonText(validCombinations);
                    validCombinations = validCombinations.sort(
                        function (a, b) {
                            if (a[0] == b[0]) {
                                if (a[1] == b[1]) {
                                    return b[2] - a[2];
                                }
                                return b[1] - a[1];
                            }
                            return b[0] - a[0];
                        }
                    );
                    let z = 0;
                    for ( let comb in validCombinations ) {
                        this.addActionButton( 'secondAction' + z + '_' + validCombinations[comb][0] + '_' + validCombinations[comb][1] + '_' + validCombinations[comb][2] , this.replaceTextWithIcons(validCombinations[comb][4]), 'onClickSecondActionPlay', null, null, 'gray');
                        z++;
                    }

                    //this.addActionButton('secondaction_confirm_button', this.translatableTexts.yes + this.replaceTextWithIcons(' - 2 [FS]'), 'onClickSecondActionPlay', null, null, 'gray');
                    this.addActionButton('secondAction_0_0_0', this.translatableTexts.no, 'onClickSecondActionPass', null, null, 'gray');
                    if (args.scepters != 0)
                        this.addActionButton( 'player_action_cancel_scepters', this.translatableTexts.cancelScepters, 'onClickCancelScepter', null, null, 'red' );
                    break;

                case 'owlChoose':
                    this.addActionButton('owlGold', this.format_block('jstpl_ressource', {
                        'size' : 'small',
                        'type' : 'gold'
                    }), 'onClickOwlGold', null, null, 'gray');
                    if (this.hasHammer( this.player_id ))
                        this.addActionButton('owlHammer', this.format_block('jstpl_ressource', {
                            'size' : 'small',
                            'type' : 'hammer'
                        }), 'onClickOwlHammer', null, null, 'gray');
                    this.addActionButton('owlFireshard', this.format_block('jstpl_ressource', {
                        'size' : 'small',
                        'type' : 'fire'
                    }), 'onClickOwlFireshard', null, null, 'gray');
                    this.addActionButton('owlMoonshard', this.format_block('jstpl_ressource', {
                        'size' : 'small',
                        'type' : 'moon'
                    }), 'onClickOwlMoonshard', null, null, 'gray');
                    this.addActionButton('ownCancel', this.translatableTexts.cancel, 'restoreServerGameState', null, null, 'red');
                    break;

                 case 'guardianChoose':
                    this.addActionButton('guardianAncient', this.format_block('jstpl_ressource', {
                        'size' : 'small',
                        'type' : 'ancient-shard'
                    }), 'onClickGuardianAncient', null, null, 'gray');
                    this.addActionButton('guardianLoyalty', this.format_block('jstpl_ressource', {
                        'size' : 'small',
                        'type' : 'loyalty'
                    }), 'onClickGuardianLoyalty', null, null, 'gray');
                    this.addActionButton('ownCancel', this.translatableTexts.cancel, 'restoreServerGameState', null, null, 'red');
                    break;

                case 'ressourceChoice':
                    var action = "actRessourceChoice";
                    this.preActionChoice( args, action );
                    break;
                case 'playerOustingChoice':
                    var action = "actOustedRessources";
                    this.preActionChoice( args, action );
                    break;
                case 'doeRessourceChoice':
                    var action = "actDoeTakeRessource";
                    this.preActionChoice( args, action );
                    break;
                case 'misfortuneChoice':
                    var action = "actMisfortuneChoice";
                    this.preActionChoice( args, action );
                    break;
                    break ;
                case 'exploitRessource':
                    var action = "";
                    if ( args.hasOwnProperty(this.player_id)
                        && args[this.player_id].action == 'side'
                    ) {
                        console.log(1);

                        if ((args.card.action != 'steal2'
                            && args.card.action != 'throwCelestialDie') && args.card.action != 'fortuneWheel'
                            && (args.card.action != 'chooseSides' || args.card.action == 'chooseSides' && args[this.player_id].mirror != 0)
                        ) {
                            console.log(2);
                            action = "actExploitRessource";
                        }
                    } else if (args.hasOwnProperty(this.player_id)) {
                        console.log(3);
                        action = "actExploitRessource";
                    }

                    this.preActionChoice( args, action );
                    break;
                case 'tritonToken':
                    this.addActionButton('tokenCancel', this.translatableTexts.cancel, 'cancelLocalStateEffects', null, null, 'red');
                    break;
                case 'exploitEffect':
                    if (args.hasOwnProperty('card')) {

                        switch (args.card.action) {
                            case 'side3x' :
                                this.addActionButton('help_button_side3x', this.translatableTexts.question, 'onClickHelpForgeButton', null, null, 'gray');
                            break;
                            case 'sideShip' :
                                this.addActionButton('help_button_sideShip', this.translatableTexts.question, 'onClickHelpForgeButton', null, null, 'gray');
                            break;
                            case 'sideMirror' :
                                this.addActionButton('help_button_sideMirror', this.translatableTexts.question, 'onClickHelpForgeButton', null, null, 'gray');
                            break;
                            case 'shieldForge' :
                                this.addActionButton('help_button_shieldForge', this.translatableTexts.question, 'onClickHelpForgeButton', null, null, 'gray');
                            break;
                            case 'forge4G':
                                this.addActionButton('forge_ship_pass_11' , this.translatableTexts.pass, 'onClickForgeNymphPass', null, null, 'red');
                                break ;
                            case 'memoryTokens':
                                console.debug(args.memory);
                                this.clientStateArgs['memory'] = Object.values(args.memory)[0];
                                this.addActionButton('memorySun', this.replaceTextWithIcons('2 [L] 1 [FS]'), 'onClickMemoryChoose', null, null, 'gray');
                                this.addActionButton('memoryMoon', this.replaceTextWithIcons('2 [AS] 1 [MS]'), 'onClickMemoryChoose', null, null, 'gray');
                                break ;
                        }
                    }
                    break;
                case 'exploitForgeBoar':
                    this.addActionButton('help_button_boarForge', this.translatableTexts.question, 'onClickHelpForgeButton', null, null, 'gray');
                    break;
                case 'useScepter':
                    console.log(this.clientStateArgs.actionData);

                    this.addActionButton('scepterFireshard', this.replaceTextWithIcons(this.clientStateArgs.actionData.amount + ' [FS]'), 'onClickScepterFireshard', null, null, 'gray');
                    this.addActionButton('scepterMoonshard', this.replaceTextWithIcons(this.clientStateArgs.actionData.amount + ' [MS]'), 'onClickScepterMoonshard', null, null, 'gray');
                    this.addActionButton('scepterCancel', this.translatableTexts.cancel, 'restoreServerGameState', null, null, 'red');
                    break ;
                case 'buyWithAncientshard':
                    //console.log(this.clientStateArgs.actionData);
                    var ressources = this.getPlayerRessources();

                    var fireCost = parseInt(dojo.byId('exploit-' + this.clientStateArgs.actionData.slot + '_item_' + this.clientStateArgs.actionData.card_id).parentElement.getAttribute('data-costfire'));
                    var moonCost = parseInt(dojo.byId('exploit-' + this.clientStateArgs.actionData.slot + '_item_' + this.clientStateArgs.actionData.card_id).parentElement.getAttribute('data-costmoon'));

                    if (((ressources.fireshard + ressources.ancientshard) < fireCost) ||
                         ((ressources.moonshard + ressources.ancientshard) < moonCost) ||
                         (moonCost != 0 && fireCost != 0 && ((ressources.moonshard + ressources.fireshard + ressources.ancientshard) < (moonCost + fireCost)))
                       )
                        {
                        this.showMessage( this.translatableTexts.notEnoughResource, 'error' );
                        this.cancelLocalStateAndClean();
                        return ;
                    }

                    var validCombinations = [];
                    if (fireCost != 0 && moonCost == 0) {
                        let combinations = [];
                        for(var i = 0; i <= fireCost; i++){
                            let text = i + ' [FS] ' + (fireCost - i) + ' [AS]';
                            combinations.push([i, 0, fireCost - i, text]);
                        }
                        validCombinations = combinations.filter(c => c[0] <= ressources.fireshard && c[2] <= ressources.ancientshard);
                    }
                    else if (fireCost == 0 && moonCost != 0) {
                        let combinations = [];
                        for(var i = 0; i <= moonCost; i++){
                          combinations.push([0, i, moonCost - i]);
                        }
                        validCombinations = combinations.filter(c => c[1] <= ressources.moonshard && c[2] <= ressources.ancientshard);
                    }
                    else {
                        let combinations = [];
                        for(var i = 0; i <= fireCost; i++) {
                            for (var j = 0; j <= moonCost; j++) {
                                combinations.push([i, j, fireCost + moonCost - i - j]);
                            }
                        }
                        validCombinations = combinations.filter(c => c[0] <= ressources.fireshard && c[1] <= ressources.moonshard && c[2] <= ressources.ancientshard);
                    }

                    if (validCombinations.length == 1) {
                        // trigger buy
                        this.onClickBuyExploitWithAncient(
                                                            {'target':
                                                                {'id' : 'exploitChoice_' + validCombinations[0][0] + '_' + validCombinations[0][1] + '_' + validCombinations[0][2]}
                                                            }
                                                            );
                    }
                    else {
                        validCombinations = this.generateButtonText(validCombinations);
                        validCombinations = validCombinations.sort(
                            function (a, b) {
                                if (a[0] == b[0]) {
                                    if (a[1] == b[1]) {
                                        return b[2] - a[2];
                                    }
                                    return b[1] - a[1];
                                }
                                return b[0] - a[0];
                            }
                        );
                        let z = 0;
                        for ( let comb in validCombinations ) {
                            this.addActionButton( 'exploitChoice' + z + '_' + validCombinations[comb][0] + '_' + validCombinations[comb][1] + '_' + validCombinations[comb][2] , this.replaceTextWithIcons(validCombinations[comb][4]), 'onClickBuyExploitWithAncient', null, null, 'gray');
                            z++;
                        }
                    }

                    this.addActionButton('tokenCancel', this.translatableTexts.cancel, 'cancelLocalStateAndClean', null, null, 'red');
                    break ;
                case 'memoryIsland':
                    this.addActionButton('tokenCancel', this.translatableTexts.cancel, 'cancelLocalStateAndClean', null, null, 'red');
                    break ;
            }
        },

        ///////////////////////////////////////////////////
        //// Utility methods

        generateButtonText: function (combinations) {
            for (let index in combinations) {
                let text = '';
                if (combinations[index][0] != 0) {
                    text += combinations[index][0] + ' [FS] ';
                }

                if (combinations[index][1] != 0) {
                    text += combinations[index][1] + ' [MS] ';
                }

                if (combinations[index][2] != 0) {
                    text += combinations[index][2] + ' [AS] ';
                }
                combinations[index][4] = text;
            }
            return combinations;
        },



        getPlayerRessources: function () {
            let ressources = { 'fireshard' : 0, 'moonshard' : 0, 'ancientshard' : 0 };

            if (dojo.byId('ancientshardcount_p' + this.player_id)) {
                ressources.ancientshard = parseInt(dojo.byId('ancientshardcount_p' + this.player_id).innerHTML);
            }

            ressources.fireshard = parseInt(dojo.byId('firecount_p' + this.player_id).innerHTML);
            ressources.moonshard = parseInt(dojo.byId('mooncount_p' + this.player_id).innerHTML);

            if (dojo.byId('scepter_fire_' +  this.player_id).innerHTML != '') {
                ressources.fireshard += parseInt(dojo.byId('scepter_fire_' + this.player_id).innerHTML.substring(1,3));
            }
            if (dojo.byId('scepter_moon_' +  this.player_id).innerHTML != '') {
                ressources.moonshard += parseInt(dojo.byId('scepter_moon_' + this.player_id).innerHTML.substring(1,3));
            }
            return ressources;
        },

        /** @Override */
        format_string_recursive : function(log, args) {
            try {
                if (log && args && !args.processed) {
                    args.processed = true;
                    if (!this.isSpectator)
                        args.You = this.divYou(); // will replace ${You} with colored version
                    var keys = [ 'ressources', 'side_type', 'old_side_type', 'sides_types', 'sides_rolled', 'gold', 'vp', 'moonshard', 'fireshard', 'ancientshard', 'loyalty' ];
                    for ( var i in keys) {
                        var key = keys[i];
                        if (typeof args[key] == 'string') {
                            var res = this.getIcons(key, args);
                            if (res) args[key] = res;
                        }
                    }

                    if (args.hasOwnProperty('ousted_player_name')) {
                        args['ousted_player_name'] = '<span style="font-weight:bold;color:#' + args['ousted_player'] + '">' + args['ousted_player_name'] + '</span>';
                    }
                }
            } catch (e) {
                console.error(log, args, "Exception thrown", e.stack);
            }
            return this.inherited(arguments);
        },

        getIcons : function(key, args) {
            switch (key) {
                case 'ressources':
                case 'gold':
                case 'vp':
                case 'moonshard':
                case 'fireshard':
                case 'ancientshard':
                case 'loyalty':
                    args[key] = this.ressourcesTextToIcon( args[key] );
                    args[key] = this.tokensTextToIcon( args[key] );
                    break;
                case 'old_side_type':
                case 'side_type':
                    args[key] = this.getSideIcon( args[key] );
                break;
                case 'sides_types':
                    var sides  = args[key].split(", ");
                    for (var i in sides)
                    {
                        var side = sides[i];

                        sides[i] = this.getSideIcon( side );
                    }
                    args[key] = sides.join(", ");
                    break;
                case 'sides_rolled':
                    var sides  = args[key].split(",");
                    for (var i in sides)
                    {
                        var side = sides[i];

                        sides[i] = this.getSideIcon( side );
                    }
                    args[key] = sides.join(" ");
                break;
            }

            return args[key];
        },

        escapeRegExp: function (str) {
          return str.replace(/[.*+?^${}()|[\]\\]/g, "\\$&"); // $& means the whole matched string
        },

        ressourcesTextToIcon: function( text )
        {
            var ressources_icons = {
                '[G]'             : 'gold',
                '[FS]'            : 'fire',
                '[MS]'            : 'moon',
                '[H]'             : 'hammer',
                '[VP]'            : 'vp',
                '[AS]'            : 'ancient-shard',
                '[L]'             : 'loyalty',
                '[M]'             : 'maze',
                '[instant]'       : 'type-instant',
                '[hourglass]'     : 'type-hourglass',
                '[reinforcement]' : 'type-cog',
                '[2players]'      : '2p',
                '[3players]'      : '3p',
                '[4players]'      : '4p',
            };

            return this.dataToIcon(ressources_icons, 'jstpl_ressource', text);
        },

        tokensTextToIcon: function( text )
        {
            var tokens_icons = {
                '[S]' : 'scepter',
            };

            return this.dataToIcon(tokens_icons, 'jstpl_token', text);
        },

        dataToIcon: function(data_source, template, text)
        {
            for (var search in data_source) {
                text = text.replace(
                    new RegExp(this.escapeRegExp(search), 'g'),
                    this.format_block(template, {
                        'size' : 'icon',
                        'type' : data_source[ search ]
                    })
                );
            }

            return text;
        },

        sidesTextToIcon: function( text ) {
            for ( var search in this.sideClass ) {
                text = text.replace(
                    new RegExp(this.escapeRegExp("[" + search + "]"), 'g'),
                    this.getSideIcon(search)
                );
            }

            return text;
        },

        getSideIcon: function( side ) {
            return this.format_block('jstpl_bside_icon', {
                'class' : this.sideClass[ side ] + ' bside-icon',
                'type'  : side,
            });
        },

        mazeTextToIcon: function( text ) {
            for ( var search in this.mazeClass ) {
                text = text.replace(
                    new RegExp(this.escapeRegExp("[" + search + "]"), 'g'),
                    this.getMazeIcon(search)
                );
            }

            return text;
        },

        getMazeIcon: function( maze ) {
            return this.format_block('jstpl_maze_icon', {
                'class' : this.mazeClass[ maze ] + ' maze-small',
                'type'  : maze,
            });
        },

        addSideStuff: function(card_div, card_type_id, card_id) {
            dojo.addClass(dojo.byId(card_id), "bside " + this.sideClass[ this.sidePosition[card_type_id] ] );
        },

        addCardStuff: function(card_div, card_type_id, card_id) {
            this.addCardClass(card_div, card_type_id, card_id);
            this.addCardTooltip(card_div, card_type_id, card_id);
        },

        addCardClass: function(card_div, card_type_id, card_id) {
            dojo.addClass( dojo.byId(card_id), "exploit" );
        },

        addCardTooltip: function(card_div, card_type_id, card_id) {
            if ( card_id.lastIndexOf('draft') != -1 ) {
                var idCard    = card_id.substr(card_id.lastIndexOf('_') + 1);
                var card_data = this.exploitTypes[idCard];
            } else if ( card_id.lastIndexOf('pile-') != -1 ) {
                var card_data = this.exploitTypes[card_type_id];
            } else {
                var idCard    = card_id.substr(card_id.lastIndexOf('_') + 1);
                var card      = this.gamedatas.exploits[card_type_id][idCard];
                var card_data = this.exploitTypes[ card.type ];
            }

            this.addTooltipHtml( card_div.id, this.getCardTooltip( card_data ) );
        },

        addPowerToolTip: function(power_div_id, type) {
            this.addTooltipHtml( power_div_id, this.format_block('jstpl_tooltip_title', {
                'description' : this.replaceTextWithIcons( _( this.exploitTypes[type].power_description ) ),
                'title'       : _( this.exploitTypes[type].name )
            } ));
        },

        addTreasureToolTip: function (position, reward) {
            var type = 'maze-' + reward;

            dojo.removeClass('maze-reward-' + position, 'hide');
            //dojo.place(this.format_block('jstpl_maze_reward', { 'location' : this.gamedatas.treasures[treasure]['location']}), 'maze-tile-' + this.gamedatas.treasures[treasure]['location']);
            dojo.addClass('maze-reward-' + position, 'token-' + type);

            switch (reward) {
                case 'vp':
                    disp = '2 [VP]';
                    break ;
                case 'fireshard':
                    disp = '1 [FS]';
                    break ;
                case 'moonshard':
                    disp = '1 [MS]';
                    break ;
            }

            this.removeTooltip('maze-tile-' + position);

            this.addTooltipHtml( 'maze-tile-' + position, this.format_block('jstpl_tooltip_maze', {
                'description' : this.ressourcesTextToIcon(_('Gain ') + disp),
                'icon'        : this.format_block('jstpl_token', {
                    'size' : 'small',
                    'type' : type,
                }),
            }));
        },

        getCardTooltip : function( data ) {
            var cost = "";
            if ( data.costMoon )
                cost += data.costMoon + " [MS] ";
            if ( data.costFire )
                cost += data.costFire + " [FS] ";

            var vp   = data.VP + " [VP] ";

            return this.format_block('jstpl_tooltip_card', {
                'title'       : _( data.name ),
                'cost'        : this.translatableTexts.tooltipCardCost + this.replaceTextWithIcons( cost ),
                'vp'          : this.translatableTexts.tooltipCardVP + this.replaceTextWithIcons( vp ),
                'description' : this.replaceTextWithIcons( _( data.description ) )
            });
        },

        replaceTextWithIcons : function( text ) {
            text = this.ressourcesTextToIcon( text );
            text = this.tokensTextToIcon( text );
            text = this.sidesTextToIcon( text );
            text = this.mazeTextToIcon( text );
            return text;
        },

        enablePlayerActionHelp : function() {
            var self = this;
            // add connexions on pool-sides, and pool-exploit
            this.connexions['player-help'] = dojo.query(".pools .bside, #board .exploit").map(function(el) {
                return dojo.connect(el, "onclick", self, 'onClickShowHelpAction' );
            });
        },

        disablePlayerActionHelp : function() {
            if ( this.connexions.hasOwnProperty("player-help") )
            {
                dojo.forEach(this.connexions["player-help"], function(el) {
                    dojo.disconnect(el);
                });
                delete this.connexions["player-help"];
            }
        },

        onClickShowHelpAction: function(event) {
            this.displayTempOverlay('page-title');
        },

        onClickShowDialog: function(event) {
            if (this.myDlg) {
                this.myDlg.show();
            }
        },

        displayTempOverlay: function(elements, args) {
            if( Object.prototype.toString.call( elements ) !== '[object Array]' )
                elements = [ elements ];

            // place overlay only when needed else it will block the whole page
            dojo.place( this.format_block('jstpl_overlay'), 'ebd-body', 'first' );

            args                 = args == undefined ? {} : args;
            args.durationFadeIn  = args.durationFadeIn == undefined ? 1000 : args.durationFadeIn;
            args.durationFadeOut = args.durationFadeOut == undefined ? 1000 : args.durationFadeOut;
            args.delay           = args.delay == undefined ? 1000 : args.delay;

            var fadeInArgs = {
                node: "df-overlay",
                duration: args.durationFadeIn,
            };

            var fadeOutArgs = {
                node: "df-overlay",
                duration: args.durationFadeOut,
                delay: args.delay,
            };

            // callback on end of fadeIn :
            // trigger fadeOut then on end of fadeOut
            // remove overlay and class to put div on top
            fadeInArgs.onEnd = function() {
                fadeOutArgs.onEnd = function() {
                    dojo.destroy('df-overlay');
                    for (var i in elements) {
                        dojo.query('#' + elements[i]).removeClass('above-overlay');
                    }
                };
                dojo.fadeOut(fadeOutArgs).play();
            };

            for (var i in elements) {
                dojo.query('#' + elements[i]).addClass('above-overlay');
            }

            dojo.fadeIn(fadeInArgs).play();
        },

        preActionChoice : function(args, action)
        {
            console.log('preActionChoice', action, args);

            if (args.hasOwnProperty(this.player_id)) {
                if (action)
                    this.prepareChoiceState({'args':args}, action);

                if (args[this.player_id].action == 'actionChoice') {
                    var take = _("Take");
                    $('pagemaintitletext').innerHTML = this.translatableTexts.shipMultipleActionChoice;
                    if (args[this.player_id].twins == true) {
                        if (args[this.player_id].hasOwnProperty('sides') && args[this.player_id]['sides'].hasOwnProperty(0)) {
                            this.addActionButton('action_getRessource1', take + ' ' + this.sidesTextToIcon( '[' + args[this.player_id]['sides'][0] + ']' ), 'onClickConfirmActionGetRessource1');
                            this.addActionButton('action_reroll1', this.replaceTextWithIcons(this.translatableTexts.reroll), 'onClickConfirmActionReroll1');
                        }
                        if (args[this.player_id].hasOwnProperty('sides') && args[this.player_id]['sides'].hasOwnProperty(1)) {
                                this.addActionButton('action_getRessource2', take + ' ' + this.sidesTextToIcon( '[' + args[this.player_id]['sides'][1] + ']' ), 'onClickConfirmActionGetRessource2');
                                this.addActionButton('action_reroll2', this.replaceTextWithIcons(this.translatableTexts.reroll), 'onClickConfirmActionReroll2');
                        }
                        if (args[this.player_id].hasOwnProperty('celestialRunning') && args[this.player_id]['celestialRunning']) {
                            this.addActionButton('action_getCelestialDie', take + ' ' + this.sidesTextToIcon( '[' + args[this.player_id]['celestialDie']+ ']' ), 'onClickConfirmActionGetCelestial');
                            this.addActionButton('action_rerollCelestial', this.replaceTextWithIcons(this.translatableTexts.reroll), 'onClickConfirmActionRerollCelestial');
                        }
                    } else if (action == 'actMisfortuneChoice') {
                        if (args[this.player_id]['sides'].hasOwnProperty(0)) {
                            this.addActionButton('action_getRessource1', this.sidesTextToIcon( '[' + args[this.player_id]['sides'][0] + ']' ), 'onClickConfirmActionGetMisfortune1');
                        }

                        if (args[this.player_id]['sides'].hasOwnProperty(1)) {
                            this.addActionButton('action_getRessource2', this.sidesTextToIcon( '[' + args[this.player_id]['sides'][1] + ']' ), 'onClickConfirmActionGetMisfortune2');
                        }
                    }
                    else {
                        //if (args[this.player_id]['sides'][0] == 'ship')
                        //    this.addActionButton('action_forgeShip1', this.sidesTextToIcon( '[ship]' ), 'onClickConfirmActionForgeShip');
                        //else
                        if (args[this.player_id].hasOwnProperty('sides') && args[this.player_id]['sides'].hasOwnProperty(0)) {
                            this.addActionButton('action_getRessource1', this.sidesTextToIcon( '[' + args[this.player_id]['sides'][0] + ']' ), 'onClickConfirmActionGetRessource1');
                        }

                        if (args[this.player_id].hasOwnProperty('sides') && args[this.player_id]['sides'].hasOwnProperty(1)) {
                            this.addActionButton('action_getRessource2', this.sidesTextToIcon( '[' + args[this.player_id]['sides'][1] + ']' ), 'onClickConfirmActionGetRessource2');
                        }
                    }

                    //this.addActionButton('action_forgeShip', this.sidesTextToIcon( '[ship]' ), 'onClickConfirmActionForgeShip');
                    //this.addActionButton('action_getRessource', this.sidesTextToIcon( '[' + args[this.player_id]['sides'][0] + ']' ), 'onClickConfirmActionGetRessource');

                } else if (args[this.player_id].action == 'cerberusToken') {
                    $('pagemaintitletext').innerHTML = this.translatableTexts.actionUseCerberusToken;
                    this.addActionButton('cerberus_yes', this.translatableTexts.yes, 'onClickCerberus');
                    this.addActionButton('cerberus_no', this.translatableTexts.no, 'onClickCerberus', null, null, 'red');
                } else if (args[this.player_id].action == 'maze') {
                    $('pagemaintitletext').innerHTML = this.translatableTexts.mazeChoosePath;
                    for (var maze in args[this.player_id].mazePath) {

                        if (args[this.player_id].hasOwnProperty('mazeTreasure') && args[this.player_id].mazePath[maze] == 'treasure') {
                            var text = args[this.player_id].mazeTreasure[maze];
                            this.addActionButton('maze_' + maze, this.mazeTextToIcon(text), 'onClickConfirmMazePath', null, null, 'gray');
                            // damn
                            if (text != '[mtreasure]') {
                                document.getElementById('maze_' + maze).querySelector('.maze-small').classList.remove('maze-small');
                            }
                        } else {
                            this.addActionButton('maze_' + maze, this.mazeTextToIcon( '[m' + args[this.player_id].mazePath[maze] + ']' ), 'onClickConfirmMazePath', null, null, 'gray');
                        }

                        var $btn = document.getElementById('maze_' + maze);

                        // highlight maze slot on button mouseover
                        if (this.connexions['hovermaze_' + maze] != undefined) {
                            dojo.disconnect(this.connexions['hovermaze_' + maze]);
                        }

                        this.connexions['hovermaze_' + maze] = dojo.connect($btn, 'onmouseover', this, function(event) {
                            var $btn = event.target.id == '' ? event.target.parentNode : event.target;
                            var mazeId = $btn.id.match(/maze_([0-9]+)/)[1];
                            document.getElementById('maze-tile-' + mazeId).classList.add('highlight');
                        });

                        // stop highlight maze slot on button mouseout
                        if (this.connexions['outmaze_' + maze] == undefined) {
                            dojo.disconnect(this.connexions['outmaze_' + maze]);
                        }

                        this.connexions['outmaze_' + maze] = dojo.connect($btn, 'onmouseout', this, function(event) {
                            var $btn = event.target.id == '' ? event.target.parentNode : event.target;
                            var mazeId = $btn.id.match(/maze_([0-9]+)/)[1];
                            document.getElementById('maze-tile-' + mazeId).classList.remove('highlight');
                        })
                    }
                } else if (args[this.player_id].action == 'mazeTreasure') {
                    $('pagemaintitletext').innerHTML = this.translatableTexts.mazeChooseTreasure;
                    for (var treasure in args[this.player_id].avTreasure) {
                        switch (treasure) {
                            case "treasure_fireshard":
                                //disp = 'maze-fs4';
                                disp = '4 [FS]';
                                break ;
                            case "treasure_moonshard":
                                //disp = 'maze-ms4';
                                disp = '4 [MS]';
                                break ;
                            case "treasure_vp":
                                //disp = 'maze-vp10';
                                disp = '10 [VP]';
                                break ;
                        }
                        //this.addActionButton('maze_' + treasure, this.format_block('jstpl_token', {
                        //                                                            'size' : 'small',
                        //                                                            'type' : disp
                        //                                                        }), 'onClickConfirmMazePath', null, null, 'gray');
                        this.addActionButton('maze_' + treasure, this.replaceTextWithIcons(disp), 'onClickConfirmMazeTreasure', null, null, 'gray');
                    }
                } else if (args[this.player_id].action == 'mazePuzzle') {
                    $('pagemaintitletext').innerHTML = this.translatableTexts.shipMultipleActionChoice;
                    this.addActionButton('mazePuzzleMaze' , this.ressourcesTextToIcon( '[M]' ), 'onClickConfirmPuzzleMaze', null, null, 'gray');
                    this.addActionButton('mazePuzzleCelestial' , this.mazeTextToIcon( '[mCelestial]' ), 'onClickConfirmPuzzleCelestial', null, null, 'gray');

                } else if (args[this.player_id].action == 'mazeConfirm') {
                    $('pagemaintitletext').innerHTML = this.translatableTexts.mazeConfirmReward;
                    // #35312
                    this.activateTritonToken(true);
                    switch (args[this.player_id].reward) {
                        case 'convert6Gto6VP':
                            this.addActionButton('mazePowerYes' , this.ressourcesTextToIcon( '-6[G] → 6[VP]' ), 'onClickConfirmMazePower', null, null, 'gray');
                            break ;
                        case 'convertMS2to8VP':
                            this.addActionButton('mazePowerYes' , this.ressourcesTextToIcon( '-2[MS] → 8[VP]' ), 'onClickConfirmMazePower', null, null, 'gray');
                            break ;
                    }
                    this.addActionButton('mazeRewardNo' , _("Do nothing"), 'onClickRejectMazePower', null, null, 'red');
                    //this.addActionButton('mazeRewardNo' , this.mazeTextToIcon( '[mCelestial]' ), 'onClickConfirmPuzzleCelestial', null, null, 'gray');

                }
            }
        },

        prepareEndGame: function() {
            if ( document.getElementsByClassName('final-card-container').length )
                return;

            // create stocks with template for each players
            var el_playarea = dojo.query('.container-play-area')[0];
            dojo.addClass(el_playarea, 'end');

            dojo.query('.roll').removeClass('roll');

            for ( var player_id in this.gamedatas.players )
            {
                var el_destination = dojo.query('#player-container-'+ player_id + " .action-row")[0];
                // delete roll class to avoid display bug
                dojo.place( this.format_block('jstpl_final_card_container', this.gamedatas.players[player_id] ), el_destination, 'after');
                dojo.addClass(el_destination, 'hide');

                var nom_container = 'final-card-p' + player_id;
                this.exploits[nom_container] = new ebg.stock();
                this.exploits[nom_container].create(this, $(nom_container) , this.exploitWidth, this.exploitHeight);
                this.exploits[nom_container].image_items_per_row = this.exploitByRow;
                this.exploits[nom_container].setSelectionMode(0);
                this.exploits[nom_container].item_margin = 5;
                this.exploits[nom_container].onItemCreate = dojo.hitch( this, 'addCardClass' );

                for (var exp_card in this.gamedatas.exploitTypes) {
                    card_exploit = this.gamedatas.exploitTypes[exp_card];
                    this.exploits[nom_container].addItemType(exp_card, 0, g_gamethemeurl + 'img/sprite-cards-reb.jpg', this.exploitSpritePosition[exp_card] * 2);
                }
                this.scoreCtrl[ player_id ].setValue( parseInt(dojo.byId('player_score_' + player_id).innerHTML));
            }
        },

        initDiceSelection: function( params )
        {
            console.log('initDiceSelection', params, this.myDlg);

            /* list of args available for diceSelector
             *
             *       selectMode           : 'flat' | 'sides'    -> display mode, select a dice or select sides
             *       nbToSelect           : int,                -> nb of dices / sides to select
             *       sameSelectable       : bool,               -> if more than 1 element muse be selected, can we select multiple time the same element ?
             *       mirrorVisible        : bool,               -> for sides mode - display mirror sides
             *       mirrorSelectable     : bool,               -> for sides mode - enable mirror selection
             *       limitSelfSelect      : bool,               -> with Satyrs card, if there is a mirror, player can only select one of his/her dices
             *       canCancel            : bool,               -> can user close/cancel the dialog
             *       title                : string,             -> dialog title
             *       onChange             : string (:function), -> another function to call onClick/onChange
             *       onConfirm            : string (:function), -> another function to call after confirm and before action function (= ajax usually)
             *       action               : string (:function), -> function name for ajax call ** MANDATORY **
             *       dices                : [ {player_id, dice_num, (is_mirror) }, (...) ]  -> list of dices / sides to use ** MANDATORY **
            **/

            // DEFAULT values
            params.selectMode           = params.selectMode == undefined || ( params.selectMode != 'flat' && params.selectMode != 'sides' ) ? 'flat' : params.selectMode;
            params.nbToSelect           = params.nbToSelect == undefined ? true : params.nbToSelect;
            params.sameSelectable       = params.sameSelectable == undefined ? false : params.sameSelectable;
            params.mirrorVisible        = params.mirrorVisible == undefined ? true : params.mirrorVisible;
            params.mirrorSelectable     = params.mirrorSelectable == undefined ? false : params.mirrorSelectable;
            params.limitSelfSelect      = params.limitSelfSelect == undefined ? false : params.limitSelfSelect;
            params.canCancel            = params.canCancel == undefined ? true : params.canCancel;
            params.title                = params.title == undefined ? _('Select something') : params.title;
            params.cardData             = params.cardData == undefined ? '' : params.cardData;
            params.onChange             = params.onChange == undefined ? '' : params.onChange;
            params.onConfirm            = params.onConfirm == undefined ? '' : params.onConfirm;
            params.action               = params.action == undefined ? '' : params.action; // SHOULD NOT BE EMPTY
            params.hideMirror           = params.hideMirror == undefined ? false : params.hideMirror;

            if ( params.dices == undefined || params.dices.length == 0 ) {
                if ( params.actionCancel != '' )
                    this[ params.actionCancel ]();
                return false;
            }

            // test
            params.canCancel = true;

            // global scope object
            this.diceSelectionArgs = params;

            // alias
            var dices = params.dices;
            var that = this;

            // if effect from a card, add it in title
            if ( params.cardData )
                params.title = _( params.cardData.name ) + " - " + params.title;

            params.title = params.title.replace( '${nb}', params.nbToSelect );

            // OLD - create dialog box, with event on hide (= cancel) + self-destroys
            // this.myDlg = new dijit.Dialog({
            //     title: params.title,
            //     closable: params.canCancel,
            //     onHide: function() {
            //         if ( params.actionCancel != '' )
            //             that[ params.actionCancel ]();
            //         that.myDlg.destroy();
            //         that.myDlg = '';
            //    },
            // });

            // -- NEW DIALOG
            this.myDlg = new ebg.popindialog();
            this.myDlg.create( 'myDialogUniqueId' );
            this.myDlg.setTitle( params.title );
            if (!params.hideMirror) {
                this.myDlg.replaceCloseCallback(function() {
                    that.myDlg.destroy();
                });
            }
            else {
                this.myDlg.replaceCloseCallback(function() {
                    that.myDlg.hide();
                    dojo.removeClass('btn-show-chooser', 'hide');
                });
            }


            if (! params.canCancel)
                this.myDlg.hideCloseIcon();

            // prepare dices clone and get their html + get players info
            var html_content = "";
            for (var index in dices)
            {
                var classes   = [];
                var diceData  = dices[index];
                original_dice = dojo.byId("player-" + diceData.player_id + "-dice-3D-" + diceData.dice).parentElement;
                dice_clone    = dojo.clone( original_dice );
                // remove classes that may make dice animate
                if (params.selectMode == "sides")
                    dojo.removeClass(dice_clone, 'roll');
                else
                    this.prepareDice(dice_clone);


                dojo.addClass(dice_clone, 'clone');
                dojo.setAttr(dice_clone, "id", "clone-" + diceData.player_id + "-" + diceData.dice);
                dojo.query(".side", dice_clone).removeClass("die-lining die-lining-fire die-lining-moon");

                classes.push( 'clickable' );
                if ( params.selectMode == "sides" && params.mirrorVisible && diceData.is_mirror ) {
                    if ( !params.mirrorSelectable )
                        classes.push( 'disabled', 'mirror' );
                } else if ( params.selectMode == "sides" && diceData.is_mirror ) {
                    continue;
                }

                html_content+= this.format_block( 'jstpl_dice_selector_item', {
                    player_id    : diceData.player_id,
                    player_name  : this.gamedatas.players[ diceData.player_id ].name,
                    player_color : this.gamedatas.players[ diceData.player_id ].color,
                    classes      : classes.join(" "),
                    dice         : diceData.dice,
                    html         : dice_clone.outerHTML
                } );
            }

            // add events
            var btn = this.format_block( 'jstpl_bga_btn', {
                color   : 'blue',
                classes : '',
                id      : 'dice-selector-confirm',
                text    : this.translatableTexts.confirm,
            } );

            html_content+= btn;

            var classes = [];

            if ( params.sameSelectable )
                classes.push( "same" );

            classes.push( params.selectMode );

            // add everything into selector template
            var html = this.format_block( 'jstpl_dice_selector', {
                html:html_content,
                classes: classes.join(" ")
            } );

            // OLD - show dialog
            // this.myDlg.attr( "content", html );
            // this.myDlg.show();

            // -- NEW DIALOG
            this.myDlg.setContent( html ); // Must be set before calling show() so that the size of the content is defined before positioning the dialog
            this.myDlg.show();

            // if card is mino, add some class
            if (params.cardData.action == 'looseThrow')
                dojo.query('.standard_popin').addClass('minotaur');

            // add events
            this.connexions['dice-selector'] = dojo.query("#dice-selector .clickable").map(function(el) {
                return dojo.connect(el, "onclick", that, 'onChangeDiceSelection' );
            });

            this.connexions['dice-selector']['confirm'] = dojo.connect( $("dice-selector-confirm") , "onclick", this, 'onConfirmDiceSelection' );

            return true;
        },

        endDiceSelection: function() {
            // deconnexion event
            if ( this.connexions.hasOwnProperty("dice-selector") )
            {
                dojo.forEach(this.connexions["dice-selector"], function(el) {
                    dojo.disconnect(el);
                });
                delete this.connexions["dice-selector"];
            }

            // empty dialog content
            this.myDlg.hide();

            // empty global object
            this.diceSelectionArgs = {};
        },

        cancelLocalStateAndClean: function () {
            this.cleanClientStateArgs();
            this.cancelLocalStateEffects();
        },

        cancelLocalStateEffects: function () {
            this.restoreServerGameState();
        },

        onClickAutoHammer: function(event) {
            dojo.stopEvent( event );
            var enabled = dojo.attr(event.target, 'data-enabled');

            this.ajaxcall('/diceforge/diceforge/actAutoHammer.html', {
                lock: true,
                'enable': enabled == 1 ? false : true,
            }, this, function( result ) {}, function( is_error ) { } );
        },

        onClickFilterRolls: function(event) {
            dojo.stopEvent( event );
            var player_name = event.target.id.match(/btn-filter-log-([a-zA-Z0-9]+)/)[1];
            var that = this;

            if (this.currentFilter == player_name) {
                this.resetFilterRolls();
                this.currentFilter = "";
                return;
            }

            this.currentFilter = player_name;
            this.resetFilterRolls();

            // convert button in blue
            dojo.query(event.target).removeClass('bgabutton_gray').addClass('bgabutton_blue');

            dojo.query(".log").forEach( function(element) {
                var text   = element.innerText || element.textContent;
                var search = that.translatableTexts.rollsLogsTextSearch.replace("${logPlayerName}", player_name);
                if ( text.indexOf( search ) == -1)
                    dojo.addClass(element, 'hide');
            });
        },



        resetFilterRolls:function()
        {
            // revert buttons in gray
            dojo.query(".btn-filter-log").removeClass('bgabutton_blue').addClass('bgabutton_gray');
            // unhide
            dojo.query(".log.hide").removeClass("hide");
        },

        prepareChoiceState: function (args, action)
        {
            console.log('prepareChoiceState', args, action);
            console.trace();

            this.clientStateArgs.sides      = args.args[this.player_id].sides;
            this.clientStateArgs.ajaxAction = action;

            //if (args.args.celestial == "celestialMirror" || args.args.celestial == "chooseSide") {
            if ((args.args.celestial == "celestialMirror" || args.args.celestial == "chooseSide")
                && (! args.args[this.player_id].hasOwnProperty('twins')
                    || (args.args[this.player_id].hasOwnProperty('twins') && args.args[this.player_id].twins == 0))
            ) {
                //console.log('dé céleste');
                switch (args.args.celestial) {
                    case 'celestialMirror':
                        this.clientStateArgs.side_choice = {'side1': true, 'side2': false};

                        var dices    = [];

                        // GET ALL DICES/SIDES BUT MIRRORS
                        for (var player_id in this.gamedatas.players) {
                            for (var i = 1 ; i <= 2 ; i++) {
                                dices.push( {
                                    "player_id" : player_id,
                                    "dice"      : i,
                                    "is_mirror" : this.lastSideUp[ player_id ][ i ].type == 'mirror' ? true : false,
                                } );
                            }
                        }

                        var args = {
                            "title"         : this.translatableTexts.celestialMirror,
                            "selectMode"    : 'sides',
                            "nbToSelect"    : 1,
                            "mirrorVisible" : false,
                            "dices"         : dices,
                            "canCancel"     : 0,
                            "action"        : 'onClickSideChoiceConfirm',
                            "hideMirror"    : true
                        };
                        this.initDiceSelection( args );
                        break ;
                    case 'chooseSide':
                        $('pagemaintitletext').innerHTML = this.translatableTexts.celestialChooseSide;
                        this.selfSides.activate('OneSide', 'onClickConfirmCelestialMirror');
                        break ;
                }
            } else if (args.args[this.player_id].firstFinish) {
                $('pagemaintitletext').innerHTML = this.translatableTexts.celestialChooseSides;
                this.selfSides.activate('OneSidePerDice', 'onClickConfirmGoddessCard');
            } else if (args.args[this.player_id].action == 'side') {
                this.clientStateArgs.side_choice = args.args[this.player_id].side_choice;

                var dices      = [];
                var nbMirror   = 0;
                var selfMirror = 0;

                if (( !args.args.hasOwnProperty('card') ) || args.args.card.action != 'oustAll') {
                    // GET ALL DICES/SIDES BUT MIRRORS
                    for (var player_id in this.gamedatas.players) {
                        if (player_id != this.player_id) {
                            for (var i = 1 ; i <= 2 ; i++) {
                                var is_mirror = this.lastSideUp[ player_id ][ i ].type == 'mirror' ? true : false;

                                dices.push( {
                                    "player_id" : player_id,
                                    "dice"      : i,
                                    "is_mirror" : is_mirror
                                } );

                                if ( is_mirror )
                                    nbMirror++;
                            }
                        }
                    }

                    // if at least one enemy mirror, add self sides
                    // AND count self mirrors too to add arguments to selector
                    if ( nbMirror ) {
                        for (var i = 1 ; i <= 2 ; i++) {
                            var is_mirror = this.lastSideUp[ this.player_id ][ i ].type == 'mirror' ? true : false;

                            dices.push( {
                                "player_id" : this.player_id,
                                "dice"      : i,
                                "is_mirror" : is_mirror
                            } );

                            if ( is_mirror )
                                selfMirror++;
                        }
                    }

                    // if we have sphinx, multiple mirror should be taken only once
                    if ( args.args.hasOwnProperty('card')  && (args.args.card.action == '4Throws' || args.args.card.action == '4ThrowsTransform') && selfMirror > 1) {
                       selfMirror = 1;
                    }

                    var argsDice = {
                        "title"           : this.translatableTexts.mirrorDialogTitle, // titre de la dialog
                        "selectMode"      : 'sides',
                        "nbToSelect"      : args.args[this.player_id].mirror,
                        "canCancel"       : 0,
                        "sameSelectable"  : (( selfMirror == 2 || args.args[this.player_id].mirror == 2 ) && args.args[this.player_id].mirror > 1)  ? 1 : 0,
                        "limitSelfSelect" : ( selfMirror == 1 && nbMirror ) ? 1 : 0,
                        "dices"           : dices,
                        "action"          : 'onClickSideChoiceConfirm',
                        "hideMirror"      : true
                    };

                    if ( args.args.hasOwnProperty('card') )
                        argsDice.cardData = args.args.card;
                }
                else if (args.args.card.action == 'oustAll') {
                    // special case if lefthand & mirror
                    // GET ALL DICES/SIDES BUT MIRRORS
                    ousted = args.args[this.player_id].oustedPlayerId;
                    for (var player_id in this.gamedatas.players) {
                        if (player_id != ousted) {
                            for (var i = 1 ; i <= 2 ; i++) {
                                var is_mirror = this.lastSideUp[ player_id ][ i ].type == 'mirror' ? true : false;

                                dices.push( {
                                    "player_id" : player_id,
                                    "dice"      : i,
                                    "is_mirror" : is_mirror
                                } );

                                if ( is_mirror )
                                    nbMirror++;
                            }
                        }
                    }

                    // if at least one enemy mirror, add self sides
                    // AND count self mirrors too to add arguments to selector
                    if ( nbMirror ) {
                        for (var i = 1 ; i <= 2 ; i++) {
                            var is_mirror = this.lastSideUp[ ousted ][ i ].type == 'mirror' ? true : false;

                            dices.push( {
                                "player_id" : ousted,
                                "dice"      : i,
                                "is_mirror" : is_mirror
                            } );

                            if ( is_mirror )
                                selfMirror++;
                        }
                    }

                    var argsDice = {
                        "title"           : this.translatableTexts.mirrorDialogTitle, // titre de la dialog
                        "selectMode"      : 'sides',
                        "nbToSelect"      : args.args[this.player_id].mirror,
                        "canCancel"       : 0,
                        "sameSelectable"  : ( selfMirror == 2 || args.args[this.player_id].mirror == 2 ) ? 1 : 0,
                        "limitSelfSelect" : ( selfMirror == 1 && nbMirror ) ? 1 : 0,
                        "dices"           : dices,
                        "action"          : 'onClickSideChoiceConfirm',
                        "hideMirror"      : true
                    };

                    if ( args.args.hasOwnProperty('card') )
                        argsDice.cardData = args.args.card;
                }

                this.initDiceSelection( argsDice );
            } else if (args.args[this.player_id].action == 'ressource' || args.args[this.player_id].action == 'mazeRessource') {

                this.triple                        = args.args[this.player_id].triple;
                this.clientStateArgs.possibilities = args.args[this.player_id].possibilities;
                this.addRessourceButtons(args.args[this.player_id].action);
            }
        },

        addRessourceButtons: function(action)
        {
            var that   = this;
            var sides  = this.clientStateArgs.sides;
            var side   = sides[0]; // We only need the first index here
            var choice = false;

            this.clientStateArgs.side_type = side.type;
            //this.mazeTextToIcon( '[m' + args[this.player_id].mazePath[maze] + ']' )
            if (this.clientStateArgs.possibilities.length > 1) {
                // create side from type
                if (side.hasOwnProperty('type'))
                    if (action == "ressource") {
                        if (side.type == 'tritonToken') {
                            $('pagemaintitletext').innerHTML = $('pagemaintitletext').innerHTML + '  <div id="type_' + side.type + '" class="token-small token-triton"></div>';
                        } else {
                            $('pagemaintitletext').innerHTML = $('pagemaintitletext').innerHTML + '  <div id="type_' + side.type + '" class="bside ' + this.sideClass[side.type]+ '"></div>';
                        }
                    } else if (action == "mazeRessource") {
                        $('pagemaintitletext').innerHTML = $('pagemaintitletext').innerHTML + this.mazeTextToIcon( '[m' + side.type + ']');
                    }

                var z = 0;
                for (var index in this.clientStateArgs.possibilities ) {
                    var possibility = this.clientStateArgs.possibilities[index];
                    possibility.text = this.replaceTextWithIcons(possibility.text);

                    this.addActionButton( 'resChoice' + z + '_' + possibility.num + '_' + possibility['[G]'] + '_' + possibility['[H]'] + '_' + possibility['[FS]'] + '_' + possibility['[MS]'] + '_' + possibility['[VP]'] + '_' + possibility['[AS]'] + '_' + possibility['[L]'] + '_' + possibility['[M]'], possibility.text, 'onClickRessourceChoice', null, null, 'gray');
                    z++;
                }
            } else if (this.clientStateArgs.possibilities.length == 1) {
                // if possibility == 1
                this.ajaxcall('/diceforge/diceforge/' + this.clientStateArgs.ajaxAction + '.html', {
                    lock: true,
                    'side'           : side.type,
                    'side-gold'      : this.clientStateArgs.possibilities[0]['[G]'],
                    'side-hammer'    : this.clientStateArgs.possibilities[0]['[H]'],
                    'side-vp'        : this.clientStateArgs.possibilities[0]['[VP]'],
                    'side-moonshard' : this.clientStateArgs.possibilities[0]['[MS]'],
                    'side-fireshard' : this.clientStateArgs.possibilities[0]['[FS]'],
                    'side-ancientshard' : this.clientStateArgs.possibilities[0]['[AS]'],
                    'side-loyalty' : this.clientStateArgs.possibilities[0]['[L]'],
                    'side-maze' : this.clientStateArgs.possibilities[0]['[M]'],
                    'sideNum'        : this.clientStateArgs.possibilities[0]['num'],
                }, this, function( result ) {}, function( is_error ) {} );
                return ;
            }
        },

        hasHammer: function ( player_id ) {
            var node = dojo.byId('hammer_container_p' + player_id);

            return dojo.hasClass(node, 'hide') ? false : true;
        },

        divYou : function() {
            var color = this.isSpectator ? '000000' : this.gamedatas.players[this.player_id].color;
            var color_bg = "";
            if (this.gamedatas.players[this.player_id] && this.gamedatas.players[this.player_id].color_back) {
                color_bg = "background-color:#" + this.gamedatas.players[this.player_id].color_back + ";";
            }
            var you = "<span style=\"font-weight:bold;color:#" + color + ";" + color_bg + "\">" +
                    __("lang_mainsite", "You") + "</span>";
            return you;
        },

        refreshStocks: function() {
            if ( !this.isEmptyObject( this.pools ) ) {
                for (var prop in this.pools) {
                    var pool = this.pools[ prop ];

                    if ( typeof pool.resetItemsPosition == 'function' ) {
                        pool.resetItemsPosition();
                    }
                }
            }

            if ( !this.isEmptyObject( this.exploits ) ) {
                for (var prop in this.exploits) {
                    var exploit = this.exploits[ prop ];

                    if ( typeof exploit.resetItemsPosition == 'function' ) {
                        exploit.resetItemsPosition();
                    }
                }
            }
        },


        activateSpecificCards: function( cards ) {
            if (!cards)
                return;

            dojo.query("#powers_p" + this.getActivePlayerId() + " .powers-small").removeClass("unusable used");

            // should only get here if current & active player
            for (var card_id in cards) {
                var element = dojo.byId("power-" + card_id);
                if ( !cards[card_id].hasOwnProperty("unusable") ) {
                    dojo.addClass(element, "usable");
                    if ( this.isCurrentPlayerActive() ) {
                        dojo.addClass(element, "clickable");
                        if (cards[card_id].type == 'companion')
                            this.connexions[ element.id ] = dojo.connect( element, 'onclick', this, 'onClickUseCompanion' );
                    }
                }
                else
                    dojo.addClass(element, "unusable");
            }
        },
        /*
         * activate listed reinforcement
         *
         * param (array) data
         *   9 : {id: "9", type: "doe"}
         *   10 : {id: "10", type: "ancient"}
         *   (...)
         *
         **/
        activateReinforcement: function( cards )
        {
            if (!cards)
                return;
            dojo.query("#powers_p" + this.getActivePlayerId() + " .powers-small").removeClass("unusable used");

            // should only get here if current & active player
            for (var card_id in cards) {
                var $el = dojo.byId('power-' + card_id);
                if (!$el)
                    continue;

                if ( !cards[card_id].hasOwnProperty('unusable') ) {
                    dojo.addClass($el, 'usable');
                    if (!cards[card_id].hasOwnProperty('choice')) {
                        cards[card_id]['choice'] = 'truc';
                    }

                    if ( this.isCurrentPlayerActive() ) {
                        dojo.addClass($el, 'clickable');
                        dojo.setAttr($el, 'data-choice', cards[card_id]['choice']);
                        this.connexions[ $el.id ] = dojo.connect( $el, 'onclick', this, 'onClickReinforcement' + this.capitalize( cards[card_id].type ) );

                    }
                } else
                    dojo.addClass($el, 'unusable');
            }

            // add class used to all active player's reinforcement
            if ( this.isCurrentPlayerActive() )
                dojo.query('#powers_p' + this.getActivePlayerId() + ' .powers-small:not(.unusable):not(.clickable)').addClass('used');
            else
                dojo.query('#powers_p' + this.getActivePlayerId() + ' .powers-small:not(.unusable):not(.usable)').addClass('used');

        },

        deactivateReinforcement: function() {
            var that = this;
            dojo.query("#powers_p" + this.getActivePlayerId() + " .powers-small").forEach( function(element) {
                dojo.removeClass(element, "clickable usable");
                if ( that.isCurrentPlayerActive() ) {
                    dojo.disconnect( that.connexions[ element.id ] );
                    delete that.connexions[ element.id ];
                }
            });
        },

        activateTritonToken: function (forceTriton = false) {
            var that = this;
            if (this.player_id == this.turnPlayerId) {
                dojo.query('#tokens_p' + this.turnPlayerId + ' .token-triton').forEach( function(element) {
                    dojo.addClass(element, "clickable");
                    if (! that.connexions.hasOwnProperty(element.id))
                        that.connexions[ element.id ] = dojo.connect( element, 'onclick', that, 'onClickUseTritonToken');
                });
                dojo.query('#tokens_p' + this.turnPlayerId + ' .token-scepter').forEach( function(element) {
                    var value = element.querySelector('.ressource-display').innerHTML;

                    if (value >= 4) {
                        element.classList.add('clickable');
                    } else {
                        element.classList.remove('clickable');
                    }

                    if (! that.connexions.hasOwnProperty(element.id))
                        that.connexions[ element.id ] = dojo.connect( element, 'onclick', that, 'onClickUseScepter');
                });
            }
            // #35312
            else if (forceTriton && this.isCurrentPlayerActive()) {
                dojo.query('#tokens_p' + this.player_id + ' .token-triton').forEach( function(element) {
                    dojo.addClass(element, "clickable");
                    if (! that.connexions.hasOwnProperty(element.id))
                        that.connexions[ element.id ] = dojo.connect( element, 'onclick', that, 'onClickUseTritonToken');
                });
            }
        },

        deactivateTritonToken: function () {
            var that = this;
            if (this.player_id == this.turnPlayerId) {
                dojo.query('#tokens_p' + this.turnPlayerId + ' .token-triton').forEach( function(element) {
                    dojo.removeClass(element, "clickable");
                    dojo.disconnect( that.connexions[ element.id ] );
                    delete that.connexions[ element.id ];
                });
            }
        },

        onClickUseTritonToken: function()
        {
            // hide other dialog that could exist
            var params = {'descriptionmyturn' : '', 'args': {}};
            params['descriptionmyturn'] = this.translatableTexts.tritonTokenDescriptionMyTurn;
            params.args[this.player_id] = {'action': 'ressource', 'triple': 1, 'sides' : [], 'possibilities': ''};
            params['args'][this.player_id]['sides'][0]= {'id': '', 'type': 'tritonToken', 'num': 0};
            params['args'][this.player_id]['possibilities'] = [];
            // display FS/MS/Gold
            params['args'][this.player_id]['possibilities'].push( {"[G]" : 0, "[H]" : 0, "[FS]" : 2, "[MS]" : 0, "[VP]" : 0, 'text' : '2[FS]', 'num': 0});
            params['args'][this.player_id]['possibilities'].push( {"[G]" : 0, "[H]" : 0, "[FS]" : 0, "[MS]" : 2, "[VP]" : 0, 'text' : '2[MS]', 'num': 0});
            params['args'][this.player_id]['possibilities'].push ({"[G]" : 6, "[H]" : 0, "[FS]" : 0, "[MS]" : 0, "[VP]" : 0, 'text' : '6[G]', 'num': 0});
            // display hammer possibilities
            if (this.hasHammer( this.player_id )) {
                params['args'][this.player_id]['possibilities'].push( {"[G]" : 5, "[H]" : 1, "[FS]" : 0, "[MS]" : 0, "[VP]" : 0, 'text' : '5[G] 1[H]', 'num': 0});
                params['args'][this.player_id]['possibilities'].push( {"[G]" : 4, "[H]" : 2, "[FS]" : 0, "[MS]" : 0, "[VP]" : 0, 'text' : '4[G] 2[H]', 'num': 0});
                params['args'][this.player_id]['possibilities'].push( {"[G]" : 3, "[H]" : 3, "[FS]" : 0, "[MS]" : 0, "[VP]" : 0, 'text' : '3[G] 3[H]', 'num': 0});
                params['args'][this.player_id]['possibilities'].push( {"[G]" : 2, "[H]" : 4, "[FS]" : 0, "[MS]" : 0, "[VP]" : 0, 'text' : '2[G] 4[H]', 'num': 0});
                params['args'][this.player_id]['possibilities'].push( {"[G]" : 1, "[H]" : 5, "[FS]" : 0, "[MS]" : 0, "[VP]" : 0, 'text' : '1[G] 5[H]', 'num': 0});
                params['args'][this.player_id]['possibilities'].push( {"[G]" : 0, "[H]" : 6, "[FS]" : 0, "[MS]" : 0, "[VP]" : 0, 'text' : '6[H]', 'num': 0});
            }

            this.removeActionButtons();
            this.setClientState("tritonToken", params);
        },

        doForge:function( player_id, old_side_id, new_side_id, new_side_type ) {
            var fromElement = dojo.query(".pools div[id$='_item_" + new_side_id + "']")[0];
            var pool        = fromElement.id.match(/pool-([0-9]+)/)[1];

            // empty destination
            var destinationParent       = document.getElementById( 'side_' + old_side_id ).parentElement;
            var destinationFlat         = document.getElementById( 'side_flat_' + old_side_id );
            var destinationFlatParent   = document.getElementById( 'side_flat_' + old_side_id ).parentElement;
            destinationParent.innerHTML = '';

            // place new_element to be slided on From element
            dojo.place( this.format_block('jstpl_bside', {
                'id'    : new_side_id,
                'class' : this.sideClass[ new_side_type ],
                'type'  : new_side_type,
            }), fromElement.id);

            dojo.place( this.format_block('jstpl_flat_bside', {
                'id'    : new_side_id,
                'class' : this.sideClass[ new_side_type ],
                'type'  : new_side_type,
            }), fromElement.id);

            dojo.destroy(destinationFlat);
            this.slideToPlus( "side_" + new_side_id, destinationParent.id ).play();
            this.slideToPlus( "side_flat_" + new_side_id, destinationFlatParent.id ).play() ;
            this.pools[ pool ].removeFromStockById(new_side_id);


            // make the dice side where we forge visible
            var d_result = dojo.query(destinationParent).closest(".dice-result")[0];
            dojo.removeClass(d_result, 'sideup1 sideup2 sideup3 sideup4 sideup5 sideup6');
            dojo.addClass( d_result, "sideup" + destinationParent.getAttribute("data-numside") );

            // update of last available dice
            var dice = destinationFlatParent.id.substr(-1, 1);
            this.lastSideUp[ player_id ][ dice ] = {
                'id'   : new_side_id,
                'type' : new_side_type
            };
        },

        getCurrentSideUp: function(player_id, dice_num) {
            return this.lastSideUp[ player_id ][ dice_num ];
        },

        activateExploits: function(slots) {
            if (!slots)
                slots = this.exploitSlot;

            for (var slot_num in slots) {
                slot = slots[slot_num];
                this.exploits[ slot ].setSelectionMode(1);
                this.connexions['exploit' + slot] = dojo.connect( this.exploits[ slot ], 'onChangeSelection', this, 'onExploitSelection' );
            }
        },

        deactivateExploits: function() {
            for (var slot_num in this.exploitSlot) {
                slot = this.exploitSlot[slot_num];
                if ( this.connexions.hasOwnProperty("exploit" + slot) ) {
                    dojo.disconnect( this.connexions["exploit" + slot] );
                    delete this.connexions["exploit" + slot];
                }
                this.exploits[ slot ].setSelectionMode(0);
            }
        },

        cleanClientStateArgs: function() {
            this.clientStateArgs = {};
        },

        onClickForgePoolSide: function( elementId ) {
            if (elementId == undefined)
                return;

            var elPool = elementId.match(/pool-([0-9]+)/)[1];

            // if in 'selected pool', no item selected then we should return
            if ( !this.pools[ elPool ].getSelectedItems().length ) {
                this.selectForge.deactivateSelfSides();

                if (this.prefs[100].value == 2 && this.selectForge.isForging == false)
                    this.activateExploits();
                return;
            }

            // unselect possible selection in other pools than selected one
            for (var pool in this.pools) {
                if ( pool != elPool && this.pools[ elPool ].getSelectedItems().length )
                    this.pools[ pool ].unselectAll();
            }

            this.selectForge.activateSelfSides();
            if (this.prefs[100].value == 2 && this.selectForge.isForging == false)
                this.deactivateExploits();
        },

        onClickForgeSelfSide: function( event, confirmed = false ) {
            var sideId = event.target.id.match(/side_flat_([0-9]+)/)[1];
            dojo.query(".bside.selected").removeClass("selected");
            dojo.query( event.target ).addClass("selected");

            // confirmation dialog if forging another side than G1
            if (dojo.getAttr(event.target.id, 'data-type') != 'G1' && !confirmed) {
                this.confirmationDialog( this.translatableTexts.forgeOriginalSide,
                        dojo.hitch( this, function() {
                           this.onClickForgeSelfSide(event, true);
                        }
                ) );
                return ;
            }

            this.selectForge.sideToReplace = sideId;

            if ( this.selectForge.getSideToForge() )
                this.onClickConfirmSideSelection();
        },

        onClickDraftButton: function( event ) {

            if( this.checkAction( "actDraft", true ) ) {
                // if stock selection exist
                var selectedCards   = this.exploits['draft'].getSelectedItems();
                var nbSelectedCards = selectedCards.length;
                if ( nbSelectedCards <= 0 ) {
                    this.showMessage( this.translatableTexts.draftNoCardSelectedError, 'error' );
                    return;
                }

                if ( nbSelectedCards > 1 ) {
                    this.showMessage( this.translatableTexts.draftTooManyCardSelectedError, 'error' );
                    return;
                }

                this.ajaxcall('/diceforge/diceforge/actDraft.html', {
                    lock : true,
                    'card_type' : selectedCards[0].type
                }, this, function( result ) {}, function( is_error ) {} );
            }
        },

        onExploitSelection: function( elementId ) {
            // console.log('onExploitSelection');
            var slot = elementId.match(/exploit-([A-Z0-9]+)/)[1];

            if ( this.exploits[slot].getSelectedItems().length ) {
                var cardId = this.exploits[slot].getSelectedItems()[0].id;

                if ( this.isTouchScreen ) {
                    this.confirmationDialog( this.translatableTexts.buyExploitConfirmation,
                        dojo.hitch( this, function() {
                           this.onClickBuyExploitCheck(cardId, slot);
                        }
                    ) );
                }
                else {
                    this.onClickBuyExploitCheck(cardId, slot);
                }
            }
        },

        playerActionCancel: function() {
            // console.log('playerActionCancel');
            this.removeActionButtons();
            this.setClientState("playerAction", this.statesInfo["playerAction"] );
        },

        onClickCerberus: function ( event ) {
            dojo.stopEvent( event );
            var use = event.target.id.match(/cerberus_([a-z]+)/)[1] == 'yes' ? 1 : 0;

            if( this.checkAction( "actUseCerberusToken") )
            {
                this.ajaxcall('/diceforge/diceforge/actUseCerberusToken.html', {
                    lock  : true,
                    'use' : use
                }, this, function( result ) {}, function( is_error ) {} );
            }
        },

        onClickConfirmGoddessCard: function( ) {
            var that = this;

            if( this.checkAction( "actSideChoice", true ) )
            {
                sides = this.pluck(this.selfSides.sidesSelected, 'type'), // ceci est un array de type
                this.ajaxcall('/diceforge/diceforge/actSideChoice.html', {
                    lock    : true,
                    'side1' : sides[0],
                    'side2' : sides[1]
                }, this, function( result ) {
                    that.selfSides.deactivate();
                }, function( is_error ) {
                    console.log(is_error);
                } );
            }
        },

        onClickConfirmCelestialMirror: function( )
        {
            var that = this;
            console.log('onClickConfirmCelestialMirror', this.selfSides);

            if( this.checkAction( "actSideChoice", true ) )
            {
                sides = this.pluck(this.selfSides.sidesSelected, 'type'), // ceci est un array de type
                sideNum = this.selfSides.sidesSelected[0].diceNum;
                sideTemp = {};
                sideTemp[1] = null;
                sideTemp[2] = null;
                sideTemp[sideNum] = sides[0];

                this.ajaxcall('/diceforge/diceforge/actSideChoice.html', {
                    lock    : true,
                    'side1' : sideTemp[1],
                    'side2' : sideTemp[2]
                }, this, function( result ) {
                    that.selfSides.deactivate();
                }, function( is_error ) {
                    console.log(is_error);
                } );
            }
        },

        onClickConfirmSideSelection: function( )
        {
            var that = this;

            if( this.checkAction( "actBuyForge", true ) )
            {
                this.ajaxcall('/diceforge/diceforge/actBuyForge.html', {
                    lock            : true,
                    'sideToForge'   : this.selectForge.getSideToForge(),
                    'sideToReplace' : this.selectForge.getSideToReplace()
                }, this, function( result ) {
                    that.selectForge.deactivateSelfSides();
                    that.selectForge.deactivatePoolSides();
                }, function( is_error ) {
                    that.selectForge.resetSelection();
                } );
            }
        },

        onClickConfirmMazePath: function (event)
        {
            var button_id = event.target.id;

            // if not ok, we search for the parent
            if (button_id.indexOf('maze') == -1)
                button_id = event.target.parentElement.id;

            button_info = button_id.split('_');

            if( this.checkAction( "actChooseMazePath") )
            {
                this.ajaxcall('/diceforge/diceforge/actChooseMazePath.html', {
                    lock                    : true,
                    newPosition             : button_info[1]
                }, this, function( result ) {}
                , function( is_error ) { } );
            }
        },

        onClickConfirmMazeTreasure: function (event)
        {
            var button_id = event.target.id;

            // if not ok, we search for the parent
            if (button_id.indexOf('maze') == -1)
                button_id = event.target.parentElement.id;

            button_info = button_id.split('_');
            if( this.checkAction( "actChooseTreasure") )
            {
                this.ajaxcall('/diceforge/diceforge/actChooseTreasure.html', {
                    lock                    : true,
                    treasure                : button_info[2]
                }, this, function( result ) {}
                , function( is_error ) { } );
            }

        },

        onClickConfirmPuzzleMaze: function (event)
        {
            dojo.stopEvent( event );

            if( this.checkAction( "actPuzzleMaze") )
            {
                this.ajaxcall('/diceforge/diceforge/actPuzzleMaze.html', {
                    lock  : true,
                }, this, function( result ) {}, function( is_error ) {} );
            }
        },

        onClickConfirmMazePower: function (event)
        {
            dojo.stopEvent( event );

            if( this.checkAction( "actMazePowerConfirm") )
            {
                this.ajaxcall('/diceforge/diceforge/actMazePowerConfirm.html', {
                    lock  : true,
                    use   : true
                }, this, function( result ) {}, function( is_error ) {} );
            }
        },

        onClickRejectMazePower: function (event)
        {
            dojo.stopEvent( event );

            if( this.checkAction( "actMazePowerConfirm") )
            {
                this.ajaxcall('/diceforge/diceforge/actMazePowerConfirm.html', {
                    lock  : true,
                    use   : false
                }, this, function( result ) {}, function( is_error ) {} );
            }
        },

        onClickConfirmPuzzleCelestial: function (event)
        {
            dojo.stopEvent( event );

            if( this.checkAction( "actPuzzleCelestial") )
            {
                this.ajaxcall('/diceforge/diceforge/actPuzzleCelestial.html', {
                    lock  : true,
                }, this, function( result ) {}, function( is_error ) {} );
            }
        },

        onClickMerchantStepOne: function (event)
        {
            var button_id = event.target.id;

            // if not ok, we search for the parent
            if (button_id.indexOf('merchant') == -1)
                button_id = event.target.parentElement.id;
            else {
                button_info = button_id.split('_');
                if (button_info[1] == "0") {
                    if( this.checkAction( "actReinforcement", true ) )
                    {
                        this.ajaxcall('/diceforge/diceforge/actReinforcement.html', {
                            lock                    : true,
                            card_id                 : this.clientStateArgs.card_id,
                            merchant_nbupgrade      : button_info[1]
                        }, this, function( result ) {}
                        , function( is_error ) { } );
                    }
                }
                else {
                    //'merchant' + i + '_' + i + '_' + j
                    this.clientStateArgs.merchantNbUpgrade = button_info[1];
                    this.setClientState("merchantSecondStep", {
                        descriptionmyturn : this.translatableTexts.merchantSecondStep
                    });
                }
            }
        },

        onClickMerchantThirdStep: function (event, bonus, arrSelected)
        {
            if (event === undefined)
                return;
            //console.debug(arrSelected);
            if (arrSelected === null || arrSelected === undefined) {
                arrSelected = [];
            }

            bonus = typeof bonus !== 'undefined' ? bonus : 0;

            met = 'onClickMerchantConfirm';

            // if change of selection
            if (dojo.query(".current-player-play-area .dice-flat .bside.selected")) {
                dojo.query(".current-player-play-area .dice-flat .bside").removeClass("selected");
                dojo.query(".pool").removeClass("clickable");
            }

            var pool = null;
            var bigPool = 0;
            var self = this;
            elementId = event.target.id;
            //dojo.query(".current-player-play-area .dice-flat .bside").removeClass("clickable");
            dojo.query("#" + elementId).addClass("selected");
            this.clientStateArgs.old_side = elementId.match(/([0-9]+)/)[1];

            // search of small pool
            side_type = dojo.getAttr(elementId, 'data-type');
            for (var i in this.sidesInit) {
                if (this.sidesInit[i].indexOf(side_type) != -1) {
                    pool = i;
                    break ;
                }
            }
            //console.log("pool " + pool);
            // definition of bigger pool
            if (pool === null)
                bigPool = 0;
            else {
                for (var i in this.initPools) {
                    if (this.initPools[i].indexOf(parseInt(pool)) != -1) {
                        bigPool = i;
                        break ;
                    }
                }
            }

            sourceBigPool = bigPool;
            //console.log("big pool " + bigPool);

            // cannot be upgraded
            if (bigPool == 7) {
                this.showMessage( this.translatableTexts.upgradeNotPossible, 'error' );
                this.restoreServerGameState();
                return ;
            }
            // add of upgrade + empty slots (recursive)
            bigPool = parseInt(bigPool) + parseInt( this.clientStateArgs.merchantNbUpgrade) + parseInt(bonus);
            //console.log("new big pool " + bigPool);

            // block upgrading if it doesn't come from celestial upgrade
            if (bigPool > 7 && this.clientStateArgs.callback != 'onClickCelestialUpgrade') {
                this.showMessage( this.translatableTexts.merchantTooMuchUpgrade, 'error' );
                return ;
            }
            else if(bigPool > 7)
                bigPool = 7;

            newBonus = bonus;

            // if we have emty slots in the first run
            if (bonus == 0) {
                //console.log("source" + sourceBigPool + " bigpool " + bigPool);
                for (var i = parseInt(sourceBigPool) + 1; i <= bigPool; i++) {
                    nbSide = 0;
                    //console.debug("i " + i);
                    for (var poolTo in this.initPools[i]) {
                        //console.debug("poolTo " + poolTo);
                        //console.debug(this.initPools[i][poolTo]);
                        if (this.pools.hasOwnProperty(this.initPools[i][poolTo]))
                            nbSide = nbSide + this.pools[this.initPools[i][poolTo]].count();
                    }
                    if (nbSide != 0) {
                        arrSelected.push( i);
                    }
                    //if (nbSide === 0)
                    //    newBonus++;
                    //else
                    //    arrSelected.push( i);
                }
            }
            /*console.log*/("new " + newBonus + " bonus " + bonus);

            // if empty slots, recursive
            if (bonus != newBonus) {
                this.onClickMerchantThirdStep(event, newBonus, arrSelected);
                return ;
            }

            if (bonus != 0) {
                arrSelected.push (bigPool);
            }

            // if ends on an empty pool, next pool
            nbSide = 0;
            for (var poolTo in this.initPools[bigPool]) {
                if (this.pools.hasOwnProperty(this.initPools[bigPool][poolTo]))
                    nbSide = nbSide + this.pools[this.initPools[bigPool][poolTo]].count();
            }

            if (nbSide == 0 && bigPool < 7) {
                this.onClickMerchantThirdStep(event, bonus + 1, arrSelected);
                return ;
            } else if (nbSide == 0 && bigPool == 7) {
                this.showMessage( this.translatableTexts.upgradeNotPossible, 'error' );
                this.restoreServerGameState();
                return ;
            }

            if (this.clientStateArgs.hasOwnProperty('callback'))
                met = this.clientStateArgs.callback;

            //console.debug(arrSelected);
            //console.log ("final bigpool " + bigPool);
            // enabling the "bigPools"
            for (var j in arrSelected) {
                popo = arrSelected[j];
                for (var poolTo in this.initPools[popo]) {
                    if (self.pools.hasOwnProperty(this.initPools[popo][poolTo])) {
                        dojo.query("#pool-" + this.initPools[popo][poolTo]).addClass("clickable");
                        self.pools[ this.initPools[popo][poolTo] ].setSelectionMode(1);
                        self.connexions['pool' + this.initPools[popo][poolTo]] = dojo.connect( self.pools[ this.initPools[popo][poolTo] ], 'onChangeSelection', self, met );
                    }
                }
            }
        },

        onClickMerchantConfirm: function( )
        {
            var that = this;
            //console.log (this.selectForge.getSideToForge() + " "  + this.clientStateArgs.old_side);

            var params = {
                    lock                    : true,
                    card_id                 : this.clientStateArgs.card_id,
                    merchant_nbupgrade      : this.clientStateArgs.merchantNbUpgrade,
                    sideToForge             : this.selectForge.getSideToForge(),
                    sideToReplace           : this.clientStateArgs.old_side
            };

            if( this.checkAction( "actReinforcement", true ))
            {
                this.ajaxcall('/diceforge/diceforge/actReinforcement.html', {
                    lock                    : true,
                    card_id                 : this.clientStateArgs.card_id,
                    merchant_nbupgrade      : this.clientStateArgs.merchantNbUpgrade,
                    sideToForge             : this.selectForge.getSideToForge(),
                    sideToReplace           : this.clientStateArgs.old_side
                }, this, function( result ) {}
                , function( is_error ) { } );
            }
            else if (this.checkAction("actCelestialUpgrade", true)) {
                this.ajaxcall('/diceforge/diceforge/actCelestialUpgrade.html', {
                    lock                    : true,
                    sideToForge             : this.selectForge.getSideToForge(),
                    sideToReplace           : this.clientStateArgs.old_side
                }, this, function( result ) {}
                , function( is_error ) { } );
            }
            return ;

        },

        onClickCancelCelestial: function ( ) {
            if (this.checkAction("actCancelCelestial", true)) {
                this.ajaxcall('/diceforge/diceforge/actCancelCelestial.html', {
                    lock  : true
                }, this, function( result ) {}
                , function( is_error ) { } );
            }
            return ;
        },

        onClickCelestialUpgrade: function( )
        {
            var that = this;
            //console.log (this.selectForge.getSideToForge() + " "  + this.clientStateArgs.old_side);

            var params = {
                    lock                    : true,
                    sideToForge             : this.selectForge.getSideToForge(),
                    sideToReplace           : this.clientStateArgs.old_side
            };

            if( this.checkAction( "actCelestialUpgrade", true ) )
            {
                this.ajaxcall('/diceforge/diceforge/actCelestialUpgrade.html', {
                    lock                    : true,
                    sideToForge             : this.selectForge.getSideToForge(),
                    sideToReplace           : this.clientStateArgs.old_side
                }, this, function( result ) {}
                , function( is_error ) { } );
            }
            return ;

        },

        onClickRessourceChoice: function (event) {
            var button_id = event.target.id;

            // if not ok, we search for the parent
            if (button_id.indexOf('resChoice') == -1)
                button_id = event.target.parentElement.id;

            if (button_id.indexOf('resChoice') != -1) {
                button_info = button_id.split('_');
                // button_info[0] : ignore
                // button_info[1] : side num
                // button_info[2] : gold
                // button_info[3] : hammer
                // button_info[4] : fireshard
                // button_info[5] : moonshard
                // button_info[6] : vp
                // 7: Ancient shard
                // 8 : Loyalty
                // 9 : Maze
                    //this.addActionButton( 'resChoice' + z + '_' + possibility.num + '_' + possibility['[G]'] + '_' + possibility['[H]'] + '_' + possibility['[FS]'] + '_' + possibility['[MS]'] + '_' + possibility['[VP]'] + '_' + possibility['[AS]'] + '_' + possibility['[L]'] + '_' + possibility['[M]'], possibility.text, 'onClickRessourceChoice', null, null, 'gray');

                var side = this.clientStateArgs.side_type;

                this.ajaxcall('/diceforge/diceforge/' + this.clientStateArgs.ajaxAction + '.html', {
                    lock: true,
                    'side'           : side,
                    'side-gold'      : button_info[2],
                    'side-hammer'    : button_info[3],
                    'side-vp'        : button_info[6],
                    'side-moonshard' : button_info[5],
                    'side-fireshard' : button_info[4],
                    'side-ancientshard' : button_info[7],
                    'side-loyalty'   : button_info[8],
                    'side-maze'      : button_info[9],
                    'sideNum'        : button_info[1],
                }, this, function( result ) {
                    // onSuccess
                    // if we are choosing ressource for the triton Token
                    if (button_info[1] == 0)
                        this.cancelLocalStateEffects();
                }, function( is_error ) {
                    // onError
                } );
            }


        },

        onClickBuyExploitWithAncient: function (event) {
            //console.debug(event);
            var button_id = event.target.id;

            // if not ok, we search for the parent
            if (button_id.indexOf('exploitChoice') == -1)
                button_id = event.target.parentElement.id;

            if (button_id.indexOf('exploitChoice') != -1) {
                button_info = button_id.split('_');
                // button_info[0] : ignore
                // button_info[1] : fireshard
                // button_info[2] : moonshard
                // button_info[3] : ancientshard

                let card_id = this.clientStateArgs.actionData.card_id;

                this.ajaxcall('/diceforge/diceforge/actBuyExploit.html', {
                    lock: true,
                    'card_id'        : card_id,
                    'fireshard'      : button_info[1],
                    'moonshard'      : button_info[2],
                    'ancientshard'   : button_info[3]
                }, this, function( result ) {
                    // onSuccess
                }, function( is_error ) {
                    // onError
                } );
            }


        },

        onClickMemoryChoose: function(event) {
            var button_id = event.target.id;

            if (button_id.indexOf('memory') == -1)
                button_id = event.target.parentElement.id;

            this.clientStateArgs['choice'] = button_id;
            this.setClientState('memoryIsland', {
                    descriptionmyturn : this.translatableTexts.memoryIsland
                });

        },

        onClickMemorySetup: function(event) {
            var button_id = event.target.id;

            if (button_id.indexOf('position') == -1)
                button_id = event.target.parentElement.id;

            button_info = button_id.split('-');
            //console.debug(this.clientStateArgs);
            //console.log(button_id);
            this.ajaxcall('/diceforge/diceforge/actMemoryToken.html', {
                    lock: true,
                    token: this.clientStateArgs.memory.key,
                    island: button_info[1],
                    choice: this.clientStateArgs.choice
            }, this, function( result ) {
                // onSuccess
            }, function( is_error ) {
                // onError
            } );

        },

        onClickSideChoiceConfirm: function ( data ) {
            //console.log('onClickSideChoiceConfirm' + data);
            if ( !data || data.length == 0 )
                return;

            var sides     = [];
            sides['lock'] = true;
            var i         = 0;

            if (this.clientStateArgs.side_choice.side1) {
                sides['side1'] = this.getCurrentSideUp( data[i].player_id, data[i].dice_num ).type;
                i++;
            }
            else
                sides['side1'] = null;

            if (this.clientStateArgs.side_choice.side2)
                sides['side2'] = this.getCurrentSideUp( data[i].player_id, data[i].dice_num ).type;
            else
                sides['side2'] = null;

            // case of mirror linked to celestial die
            if (this.clientStateArgs.side_choice.side98)
                sides['side98'] = this.getCurrentSideUp( data[i].player_id, data[i].dice_num ).type;
            else
                sides['side98'] = null;

            if( this.checkAction( "actSideChoice" ) ) {
                this.ajaxcall('/diceforge/diceforge/actSideChoice.html', sides, this, function( result ) {
                    // onSuccess
                    this.endDiceSelection();
                }, function( is_error ) {
                    // onError
                } );
            }
        },

        onClickEndForge: function( event )
        {
            // console.log('onClickEndForge');
            dojo.stopEvent( event );
            if( this.checkAction( "actEndForge" ) ) {
                this.ajaxcall('/diceforge/diceforge/actEndForge.html', { lock: true }, this, function( result ) {}, function( is_error ) { } );
            }
        },

        onClickCancelForge: function( event ) {
            dojo.stopEvent( event );
            this.playerActionCancel();
        },

        onClickCancelPlayerAction: function( event ) {
            dojo.stopEvent( event );
            this.playerActionCancel();
        },

        onClickHelpForgeButton: function( event ) {
            dojo.stopEvent( event );
            var side = event.target.id.match(/help_button_([a-zA-Z0-9]+)/)[1];

            switch ( side ) {
                case 'side3x':
                case 'sideShip':
                case 'sideMirror':
                case 'boarForge':
                    this.displayTempOverlay(['dice1-flat-player-' + this.player_id, 'dice2-flat-player-' + this.player_id]);
                    var el = document.getElementById('dice1-flat-player-' + this.player_id);
                    window.scrollTo( {
                        'left'     : el.offsetLeft,
                        'top'      : el.offsetTop,
                        'behavior' :'smooth',
                    } );
                    break;

                case 'shieldForge':
                    this.displayTempOverlay('pool-12');
                    var el = document.getElementById('pool-12');
                    window.scrollTo( {
                        'left'     : el.offsetLeft,
                        'top'      : el.offsetTop,
                        'behavior' :'smooth',
                    } );
                    break;

            }
        },

        onClickPlayerAction: function( event, confirm )
        {
            // console.log('onClickPlayerAction');
            dojo.stopEvent( event );
            confirm = typeof confirm !== 'undefined' ? confirm : false;
            var action = event.target.id.match(/player_action_button_([a-zA-Z]+)/)[1];

            if ( action == "forge" ) {
                this.gotoChooseForge();
            }
            else if ( action == "exploit" ) {
                this.gotoChooseExploit();
            }
            else if ( action == "end" ) {
                if (!confirm) {
                    this.confirmationDialog( this.translatableTexts.confirmEndTurn,
                        dojo.hitch( this, function() {
                           this.onClickPlayerAction(event, true);
                        }
                    ) );
                }
                else {
                    if( this.checkAction( "actEndPlayerTurn" ) ) {
                        this.ajaxcall('/diceforge/diceforge/actEndPlayerTurn.html', { lock: true }, this, function( result ) {}, function( is_error ) { } );
                    }
                }
            }
        },

        gotoChooseForge: function( params )
        {
            // console.log('gotoChooseForge')
            params = params == undefined ? {} : params;
            params.descriptionmyturn = params.hasOwnProperty('descriptionmyturn') ? params.descriptionmyturn : this.translatableTexts.forgeDescriptionMyTurn;

            this.removeActionButtons();
            this.setClientState("chooseForge", params);
        },

        gotoChooseExploit: function( params )
        {
            // console.log('gotoChooseExploit')
            params = params == undefined ? {} : params;
            params.descriptionmyturn = params.hasOwnProperty('descriptionmyturn') ? params.descriptionmyturn : this.translatableTexts.exploitDescriptionMyTurn;

            this.removeActionButtons();
            this.setClientState("chooseExploit", params);
        },

        ///////////////////////////////////////////////////
        //// Player's action

        onClickBoarPlayer: function (event)
        {
            var choosenId = event.target.parentNode.id.split('_')[2];

            if (choosenId == undefined)
                choosenId = event.target.id.split('_')[2];;

            if (choosenId != undefined && this.checkAction("actExploitBoar")) {
                this.ajaxcall('/diceforge/diceforge/actExploitBoar.html', { lock: true, "forgePlayerId": choosenId }, this, function( result ) {}, function( is_error ) { } );
            }

        },
        onClickReinforcementDoe: function (event)
        {
            dojo.stopEvent( event );
            var el      = event.target;
            var card_id = el.id.match(/power-([0-9]+)/)[1];

            this.clientStateArgs.actionData = { "card_id" : card_id };

            var args = {
                "title"      : this.translatableTexts.silverHindDialogTitle,
                "selectMode" : 'flat',
                "nbToSelect" : 1,
                "dices"      : [
                    {
                        "player_id" : this.player_id,
                        "dice"      : 1
                    },
                    {
                        "player_id" : this.player_id,
                        "dice"      : 2
                    }
                ],
                "action"     : 'onDiceSelectionDoe'
            };

            this.initDiceSelection( args );
        },

        onClickReinforcementOracle: function (event)
        {
            dojo.stopEvent( event );
            var el      = event.target;
            var card_id = el.id.match(/power-([0-9]+)/)[1];

            this.clientStateArgs.actionData = { "card_id" : card_id };

            var args = {
                "title"      : this.translatableTexts.oracleHindDialogTitle,
                "selectMode" : 'flat',
                "nbToSelect" : 1,
                "dices"      : [
                    {
                        "player_id" : this.player_id,
                        "dice"      : 1
                    },
                    {
                        "player_id" : this.player_id,
                        "dice"      : 2
                    }
                ],
                "action"     : 'onDiceSelectionDoe'
            };

            this.initDiceSelection( args );
        },

        onClickUseScepter: function (event)
        {
            dojo.stopEvent( event );
            var el = event.target;

            // Management if click on the number
            if (!el.id.match(/token-scepter-([0-9]+)-([0-9]+)/))
                return;

            var card_id = el.id.match(/token-scepter-([0-9]+)-([0-9]+)/)[2];

            var solde = dojo.byId('countscepter_' + card_id).innerHTML;
            var am = 0;

            if (solde < 4)
                // do nothing as below threshold
                return ;
            else if (solde < 6)
                am = 1;
            else
                am = 2;

            this.clientStateArgs.actionData = { "scepter_id" : card_id, "amount" : am };
            this.setClientState('useScepter', {
                descriptionmyturn : this.translatableTexts.scepterTokenDescriptionMyTurn
            });
        },

        onClickScepterFireshard: function (data) {
            this.onClickScepter("fireshard");
        },

        onClickScepterMoonshard: function (data) {
            this.onClickScepter("moonshard");
        },

        onClickScepter: function (resource)
        {
            if( this.checkAction( "actUseScepter" ) ) {
                this.ajaxcall('/diceforge/diceforge/actUseScepter.html', {
                    lock: true,
                    'scepter_id': this.clientStateArgs.actionData.scepter_id,
                    'resource_type': resource
                }, this, function( result ) {
                }, function( is_error ) {
                } );
            }
        },

        onClickCancelScepter: function (data)
        {
            if( this.checkAction( "actCancelScepter" ) ) {
                this.ajaxcall('/diceforge/diceforge/actCancelAllScepters.html', {
                    lock: true,
                }, this, function( result ) {
                }, function( is_error ) {
                } );
            }
        },

        onDiceSelectionDoe: function ( data )
        {
            if( this.checkAction( "actReinforcement" ) ) {
                this.ajaxcall('/diceforge/diceforge/actReinforcement.html', {
                    lock: true,
                    'card_id'  : this.clientStateArgs.actionData.card_id,
                    'dice_num' : data[0].dice_num
                }, this, function( result ) {
                    // onSuccess
                    this.endDiceSelection();
                }, function( is_error ) {
                    // onError
                } );
            }
        },

        onDiceSelectionAncestor: function ( data )
        {
            if( this.checkAction( "actAncestorSelect" ) ) {
                this.ajaxcall('/diceforge/diceforge/actAncestorSelect.html', {
                    lock: true,
                    'dice_num' : data[0].dice_num
                }, this, function( result ) {
                    // onSuccess
                    this.endDiceSelection();
                }, function( is_error ) {
                    // onError
                } );
            }
        },

        onDiceSelection4Throws: function ( data )
        {
            if( this.checkAction( "actExploitEnigma" ) ) {
                this.ajaxcall('/diceforge/diceforge/actExploitEnigma.html', {
                    lock: true,
                    'die_number' : parseInt(data[0].dice_num)
                }, this, function( result ) {
                    // onSuccess
                    this.endDiceSelection();
                }, function( is_error ) {
                    // onError
                } );
            }
        },
        //TODO: rendre générique :)
        onClickReinforcementAncient: function (event)
        {
            // console.log('onClickReinforcementAncient');
            dojo.stopEvent( event );
            var el      = event.target;
            var card_id = el.id.match(/power-([0-9]+)/)[1];

            if( this.checkAction( "actReinforcement" ) ) {
                var that = this;

                this.ajaxcall('/diceforge/diceforge/actReinforcement.html', {
                    lock: true,
                    'card_id': card_id
                }, this, function( result ) {
                    // onSuccess
                }, function( is_error ) {
                    // onError
                    // console.log('onerror1')
                    // var state = that.getKeys( that.statesInfo );
                    // that.setClientState( state[0], that.statesInfo[ state[0] ] );
                } );
            }
        },
        onClickReinforcementLight: function (event)
        {
            // console.log('onClickReinforcementAncient');
            dojo.stopEvent( event );
            var el      = event.target;
            var card_id = el.id.match(/power-([0-9]+)/)[1];

            this.clientStateArgs.side_choice = {'side1': true, 'side2': false};
            this.clientStateArgs.reinforcement = card_id;
            var dices    = [];

            // GET ALL DICES/SIDES BUT MIRRORS
            for (var player_id in this.gamedatas.players) {
                for (var i = 1 ; i <= 2 ; i++) {
                    var is_mirror = this.lastSideUp[ player_id ][ i ].type == 'mirror' ? true : false;

                    dices.push( {
                        "player_id" : player_id,
                        "dice"      : i,
                        "is_mirror" : is_mirror
                    } );

                }
            }

            var args = {
                "title"      : _("Select a side"),
                "selectMode" : 'sides',
                "nbToSelect" : 1,
                "dices"      : dices,
                "canCancel"  : 0,
                "action"     : 'onClickSelectLight'
            };
            this.initDiceSelection( args );

            //if( this.checkAction( "actReinforcement" ) ) {
            //    var that = this;
            //
            //    this.ajaxcall('/diceforge/diceforge/actReinforcement.html', {
            //        lock: true,
            //        'card_id': card_id,
            //        // TODO!!
            //        'dice_num': 23
            //    }, this, function( result ) {
            //        // onSuccess
            //    }, function( is_error ) {
            //        // onError
            //        // console.log('onerror1')
            //        // var state = that.getKeys( that.statesInfo );
            //        // that.setClientState( state[0], that.statesInfo[ state[0] ] );
            //    } );
            //}
        },

        onClickSelectLight: function(data) {
            if ( !data || data.length == 0 )
                return;

            var sides     = [];
            sides['lock'] = true;

            side = this.getCurrentSideUp( data[0].player_id, data[0].dice_num ).id;

            if( this.checkAction( "actReinforcement" ) ) {
                var that = this;

                this.ajaxcall('/diceforge/diceforge/actReinforcement.html', {
                    lock: true,
                    'card_id': this.clientStateArgs.reinforcement,
                    'dice_num': side
                }, this, function( result ) {
                    // onSuccess
                    this.endDiceSelection();
                }, function( is_error ) {
                    // onError
                    // console.log('onerror1')
                    // var state = that.getKeys( that.statesInfo );
                    // that.setClientState( state[0], that.statesInfo[ state[0] ] );
                } );
            }
        },

        onClickReinforcementMerchant: function (event)
        {
            dojo.stopEvent( event );
            var el      = event.target;
            var card_id = el.id.match(/power-([0-9]+)/)[1];

            this.clientStateArgs.card_id = card_id;
            this.clientStateArgs.merchantNbUpgrade = dojo.query("#power-" + card_id)[0].innerHTML;
            if (this.clientStateArgs.merchantNbUpgrade == "")
                this.clientStateArgs.merchantNbUpgrade = 1;
            this.setClientState("merchantFirstStep", {
                    descriptionmyturn : this.translatableTexts.merchantFirstStep
            });
            return ;
        },

        onClickReinforcementTree: function (event) {
            // console.log('onClickReinforcementAncient');
            dojo.stopEvent( event );
            var el      = event.target;
            var choice = dojo.attr(el, 'data-choice');
            var card_id = el.id.match(/power-([0-9]+)/)[1];

            if (choice == 'false') {
                // trigger the classical action
                this.actReinforcementTree(card_id, null);
            }
            else {
                 this.addActionButton( card_id + '_gold', this.replaceTextWithIcons('3 [G] 1 [VP]'), 'onClickConfirmTree', null, null, 'gray');
                 this.addActionButton( card_id + '_vp', this.replaceTextWithIcons('2 [VP]'), 'onClickConfirmTree', null, null, 'gray');
            }

        },

        onClickConfirmTree: function (event) {
            dojo.stopEvent( event );
            var el      = event.target;

            button_info = el.id.split('_');
            this.actReinforcementTree(button_info[0], button_info[1]);

        },

        actReinforcementTree: function (card_id, choice) {
            if( this.checkAction( "actReinforcement" ) ) {
                var that = this;

                this.ajaxcall('/diceforge/diceforge/actReinforcement.html', {
                    lock: true,
                    'card_id': card_id,
                    'owl': choice
                }, this, function( result ) {
                    // onSuccess
                }, function( is_error ) {
                    // onError
                    // console.log('onerror1')
                    // var state = that.getKeys( that.statesInfo );
                    // that.setClientState( state[0], that.statesInfo[ state[0] ] );
                } );
            }
        },

        onClickUseCompanion: function (event) {
            dojo.stopEvent( event );
            this.confirmationDialog( this.translatableTexts.convertCompanion,
                    dojo.hitch( this, function() {
                       this.onClickConfirmCompanion(event);
                    }
                ) );
        },

        onClickConfirmCompanion: function (event) {
            var el      = event.target;
            var card_id = el.id.match(/power-([0-9]+)/)[1];

            if( this.checkAction( "actUseCompanion" ) ) {
                this.ajaxcall('/diceforge/diceforge/actUseCompanion.html', {
                    lock: true,
                    'card_id': card_id
                }, this, function( result ) {
                    // onSuccess
                }, function( is_error ) {
                    // onError
                } );
            }
        },

        onClickReinforcementCompanion: function (event) {
            dojo.stopEvent( event );
            var el      = event.target;
            var card_id = el.id.match(/power-([0-9]+)/)[1];

            if( this.checkAction( "actReinforcement" ) ) {
                var that = this;

                this.ajaxcall('/diceforge/diceforge/actReinforcement.html', {
                    lock: true,
                    'card_id': card_id
                }, this, function( result ) {
                    // onSuccess
                }, function( is_error ) {
                    // onError
                    // console.log('onerror1')
                    // var state = that.getKeys( that.statesInfo );
                    // that.setClientState( state[0], that.statesInfo[ state[0] ] );
                } );
            }
        },
        onClickReinforcementNymphe: function (event) {
            // console.log('onClickReinforcementNymphe');
            dojo.stopEvent( event );
            var el      = event.target;
            var card_id = el.id.match(/power-([0-9]+)/)[1];

            if( this.checkAction( "actReinforcement" ) ) {
                var that = this;

                this.ajaxcall('/diceforge/diceforge/actReinforcement.html', {
                    lock: true,
                    'card_id': card_id
                }, this, function( result ) {
                    // onSuccess
                }, function( is_error ) {
                    // onError
                    // console.log('onerror2');
                    // console.log(is_error);
                    // var state = that.getKeys( that.statesInfo );
                    // that.setClientState( state[0], that.statesInfo[ state[0] ] );
                } );
            }
        },
        onClickReinforcementOwl: function (event) {
            // console.log('onClickReinforcementOwl');
            var el      = event.target;
            var card_id = el.id.match(/power-([0-9]+)/)[1];

            this.clientStateArgs.card_id = card_id;
            this.setClientState("owlChoose", {
                descriptionmyturn : this.translatableTexts.owlDescriptionMyTurn
            });
        },

        onClickReinforcementGuardian: function (event) {
            // console.log('onClickReinforcementOwl');
            var el      = event.target;
            var card_id = el.id.match(/power-([0-9]+)/)[1];

            this.clientStateArgs.card_id = card_id;
            this.setClientState("guardianChoose", {
                descriptionmyturn : this.translatableTexts.owlDescriptionMyTurn
            });
        },

        onClickOwlMoonshard: function (event) {
            this.onRessourceSelectionOwl('moonshard');
        },
        onClickOwlFireshard: function (event) {
            this.onRessourceSelectionOwl('fireshard');
        },
        onClickOwlGold: function (event) {
            this.onRessourceSelectionOwl('gold');
        },
        onClickOwlHammer: function (event) {
            this.onRessourceSelectionOwl('hammer');
        },

        onClickGuardianAncient: function (event) {
            this.onRessourceSelectionOwl('ancient');
        },

        onClickGuardianLoyalty: function (event) {
            this.onRessourceSelectionOwl('loyalty');
        },

        onRessourceSelectionOwl: function (ressource)
        {
            // console.log('onRessourceSelectionOwl');
            if( this.checkAction( "actReinforcement" ) ) {
                this.ajaxcall('/diceforge/diceforge/actReinforcement.html', {
                    lock: true,
                    'card_id': this.clientStateArgs.card_id,
                    'owl': ressource
                }, this, function( result ) {
                }, function( is_error ) {
                } );
            }
        },
        onClickConfirmActionReroll1: function (event) {
            this.onClickConfirmAction('reroll', 1);
        },
        onClickConfirmActionReroll2: function (event) {
            this.onClickConfirmAction('reroll', 2);
        },
        onClickConfirmActionRerollCelestial: function (event) {
            this.onClickConfirmAction('rerollCelestial', 0);
        },
        onClickConfirmActionForgeShip: function (event) {
           this.onClickConfirmAction('forge', 0);
        },
        onClickConfirmActionGetRessource: function (event) {
           this.onClickConfirmAction('ressource', 0);
        },
        onClickConfirmActionGetRessource1: function (event) {
           this.onClickConfirmAction('ressource', 1);
        },
        onClickConfirmActionGetRessource2: function (event) {
           this.onClickConfirmAction('ressource', 2);
        },
        onClickConfirmActionGetCelestial: function (event) {
            this.onClickConfirmAction('celestial', 0);
        },
        onClickConfirmActionGetMisfortune1: function (event) {
            this.onClickConfirmMisfortune('ressource', 1);
        },
        onClickConfirmActionGetMisfortune2: function (event) {
            this.onClickConfirmMisfortune('ressource', 2);
        },

        onClickConfirmMisfortune: function (action, die)
        {
            // console.log('onClickConfirmAction');
            if( this.checkAction( "actActionMisfortune" ) ) {
                this.ajaxcall('/diceforge/diceforge/actActionMisfortune.html', {
                    lock:true,
                    'actionChoice': action,
                    'die' : die
                }, this, function( result ) {
                }, function( is_error ) {
                    // onError
                } );
            }
        },

        onClickConfirmAction: function (action, die)
        {
            // console.log('onClickConfirmAction');
            if( this.checkAction( "actActionChoice" ) ) {
                this.ajaxcall('/diceforge/diceforge/actActionChoice.html', {
                    lock:true,
                    'actionChoice': action,
                    'die' : die
                }, this, function( result ) {
                }, function( is_error ) {
                    // onError
                } );
            }
        },

        onClickReinforcementPrePass: function (event)
        {
            // console.log('onClickReinforcementPrePass');
            // if no reinforcement used while many
            if ( dojo.query(".current-player-play-area .powers-small.used").length != dojo.query(".current-player-play-area .powers-small").length ){
                this.confirmationDialog( this.translatableTexts.passReinforcementConfirmation,
                    dojo.hitch( this, function() {
                       this.onClickReinforcementPass();
                    }
                ) );
            } else {
                this.onClickReinforcementPass();
            }
        },

        onClickReinforcementPass: function (event)
        {
            // console.log('onClickReinforcementPass');
            if( this.checkAction( "actReinforcementPass" ) ) {

                this.ajaxcall('/diceforge/diceforge/actReinforcementPass.html', {
                    lock:true
                }, this, function( result ) {
                }, function( is_error ) {
                    // onError
                } );
            }
        },

        onClickSecondActionPass: function (event)
        {
            this.onClickSecondAction(false, event);
        },
        onClickSecondActionPlay: function (event)
        {
            this.onClickSecondAction(true, event);
        },
        onClickSecondAction: function (is_play, event)
        {
            var button_id = event.target.id;
            console.log(button_id);
            // if not ok, we search for the parent
            if (button_id.indexOf('secondAction') == -1)
                button_id = event.target.parentElement.id;

            if (button_id.indexOf('secondAction') != -1) {
                button_info = button_id.split('_');
                // button_info[0] : ignore
                // button_info[1] : fireshard
                // button_info[2] : moonshard
                // button_info[3] : ancientshard

                if( this.checkAction( "actSecondAction" ) ) {
                    this.ajaxcall('/diceforge/diceforge/actSecondAction.html', {
                        lock:true,
                        'play': is_play,
                        'fireshard' : button_info[1],
                        'ancientshard' : button_info[3]
                    },this, function( result ) {}, function( is_error ) { } );
                }

            }

        },

        onClickForgeShipPass: function (event)
        {
            dojo.stopEvent(event);
            var num = event.target.id.match(/forge_ship_pass_([0-9]+)/)[1];

            if( this.checkAction( "actForgeShipPass" ) )
            {
                this.ajaxcall('/diceforge/diceforge/actForgeShipPass.html', {
                    lock:true,
                    'sideNum': num
                },this, function( result ) {}, function( is_error ) { } );
            }
        },

        onClickForgeNymphPass: function (event)
        {
            dojo.stopEvent(event);
            //var num = event.target.id.match(/forge_ship_pass_([0-9]+)/)[1];

            if( this.checkAction( "actForgeNymphPass" ) )
            {
                this.ajaxcall('/diceforge/diceforge/actForgeNymphPass.html', {
                    lock:true
                    //'sideNum': num
                },this, function( result ) {}, function( is_error ) { } );
            }
        },

        onClickBuyExploitCheck: function (card_id, slot) {
            //var that = this;
            //var ancientdiv = dojo.byId('ancientshardcount_p' + this.player_id);

            //if (ancientdiv != null && ancientdiv.innerHTML != 0) {
            // free as coming from titan card
            if (this.clientStateArgs.free) {
                this.onClickBuyExploit(card_id, slot);
            }
            else if (this.getAvailableAncientShard() != 0) {
                this.clientStateArgs.actionData = { "card_id" : card_id, "slot" : slot };
                this.setClientState('buyWithAncientshard', {
                    descriptionmyturn : this.translatableTexts.buyWithAncientShardDescriptionMyTurn
                });
            }
            else
                this.onClickBuyExploit(card_id, slot);

            return ;
        },

        getAvailableAncientShard: function () {
            let ancientdiv = dojo.byId('ancientshardcount_p' + this.player_id);
            if (ancientdiv != null && ancientdiv.innerHTML != 0)
                return ancientdiv.innerHTML;
            else
                return 0;
        },

        onClickBuyExploit: function (card_id, slot)
        {
            var that = this;
            console.log("too");
            if( this.checkAction( "actBuyExploit" ) ) {
                this.ajaxcall('/diceforge/diceforge/actBuyExploit.html', {
                        lock:true,
                        'card_id': card_id,
                        'fireshard'      : 0,
                        'moonshard'      : 0,
                        'ancientshard'   : 0
                    },this,
                    function( result ) {
                        // on success
                    },
                    function( is_error ) {
                        // on error
                        that.exploits[ slot ].unselectItem( card_id );
                    }
                );
            }

            if (this.clientStateArgs.free) {
                this.clientStateArgs.free = false;
            }
        },

        onChangeDiceSelection: function( event )
        {
            var elTarget     = dojo.query( event.target ).closest('.clickable')[0];
            var targetInfo   = elTarget.id.match(/dice-num-([0-9]+)-([0-9]+)/);
            var elsSelected  = dojo.query("#dice-selector .selected");
            var spanSelected = this.getSpanQuantityDiceSelection();
            var nbSelected   = elsSelected.length;

            if ( dojo.hasClass( elTarget, 'disabled' ) )
                return;
//console.debug(this.diceSelectionArgs);
                //console.log("nbsElect " + nbSelected + " spanSel" + spanSelected + " targentIno " + targetInfo);

            // if self dice selection is limited:
            // if not selected yet and target is self, then disable the other dice
            if ( this.diceSelectionArgs.limitSelfSelect == true) {
                // if not self dice selected yet, make other self dice not selectable
                // first check if a self dice is in elements yet selected
                var selfFound = false;
                if ( nbSelected ) {
                    var that = this;
                    dojo.forEach( elsSelected, function(element, i) {
                        if ( selfFound )
                            return;

                        var infos = element.id.match(/dice-num-([0-9]+)-([0-9]+)/);
                        if (infos[1] == that.player_id)
                            selfFound = infos;
                    });
                }

                // if self selected yet and target is self
                var otherSelfDiceNum = targetInfo[2] == 1 ? 2 : 1;
                var elOtherDice      = dojo.query("#dice-selector #dice-num-" + this.player_id + "-" + otherSelfDiceNum )[0];
                if ( selfFound && targetInfo[1] == selfFound[1] && targetInfo[2] == selfFound[2] ) {

                    // check if mirror and if it should stay disabled or not
                    if ( dojo.hasClass( elOtherDice, 'mirror') && !this.diceSelectionArgs.mirrorSelectable ) {
                        // nothing
                    }
                    else
                        dojo.removeClass( elOtherDice , 'disabled' );
                }
                // else if target dice is self one, disable other dice
                else if ( targetInfo[1] == this.player_id )
                    dojo.addClass( elOtherDice, 'disabled' );
            }

            // if there is only one dice to select, juste swap selected class
            if ( this.diceSelectionArgs.nbToSelect == 1 ) {
                elsSelected.removeClass( 'selected' );
                dojo.addClass( elTarget, 'selected' );
            }
            else {
                // if nb = max
                if ( this.diceSelectionArgs.nbToSelect == elsSelected.length || this.diceSelectionArgs.nbToSelect == spanSelected ) {
                    // if max and target selected and sameSelectable : just decrease the num
                    if ( dojo.hasClass( elTarget, 'selected' ) && this.diceSelectionArgs.sameSelectable )
                        this.decreaseDiceSelection( elTarget );
                    // if max and target selected and NOT sameSelectable : then unselect
                    else if ( dojo.hasClass( elTarget, 'selected' ) )
                        dojo.removeClass( elTarget, 'selected' );
                }
                // if nb not max
                else {
                    if ( this.diceSelectionArgs.sameSelectable )
                        this.increaseDiceSelection( elTarget );
                    else
                        if ( dojo.hasClass( elTarget, 'selected' ) )
                            dojo.removeClass( elTarget, 'selected' );
                        else
                            dojo.addClass( elTarget, 'selected' );
                }
            }

            // callback
            if ( typeof this.diceSelectionArgs.onChange === "function" )
                this[ this.diceSelectionArgs.onChange ]();
        },

        increaseDiceSelection: function(element)
        {
            var elNum   = dojo.query(element).query(".num")[0];
            var textNum = elNum.innerText || elNum.textContent;

            textNum         = textNum == "" ? 1 : parseInt(textNum) + 1;
            elNum.innerHTML = textNum;
            //element.addClass( 'selected' );
            dojo.addClass( element, 'selected' );
        },

        decreaseDiceSelection: function(element)
        {
            // if number = 0 remove .selected
            var elNum   = dojo.query(element).query(".num")[0];
            var textNum = elNum.innerText || elNum.textContent;

            if ( textNum == "" )
                return;

            textNum = parseInt(textNum) - 1;

            if ( textNum == 0 )
            {
                textNum = "";
                //element.removeClass( 'selected' );
                dojo.removeClass( element, 'selected' );
            }

            elNum.innerHTML = textNum;
        },

        onConfirmDiceSelection: function( event )
        {
            var nbSelected = 0;
            nbSelected = this.diceSelectionArgs.sameSelectable ? this.getSpanQuantityDiceSelection() : dojo.query("#dice-selector .selected").length;
//console.log("same " + this.diceSelectionArgs.sameSelectable + "span quant " + this.getSpanQuantityDiceSelection() + " else lenght " + dojo.query("#dice-selector .selected").length);
            if ( this.diceSelectionArgs.nbToSelect != nbSelected )
                return;

            var result = [];
            if ( this.diceSelectionArgs.sameSelectable )
            {
                dojo.query("#dice-selector .selected .num").forEach( function(element) {
                    var qty = element.innerText || element.textContent;
                    qty = qty == "" ? 0 : parseInt( qty );

                    if ( qty ) {
                        selectionEl = dojo.query(element).closest(".selected")[0];
                        for ( i = 1 ; i <= qty ; i++ ) {
                            diceInfo = selectionEl.id.match(/dice-num-([0-9]+)-([0-9]+)/);

                            result.push( {
                                'player_id' : diceInfo[1],
                                'dice_num'  : diceInfo[2],
                            } );
                        }
                    }
                });
            }
            else
            {
                dojo.query("#dice-selector .selected").forEach( function(element) {
                    diceInfo = element.id.match(/dice-num-([0-9]+)-([0-9]+)/);

                    result.push( {
                        'player_id' : diceInfo[1],
                        'dice_num'  : diceInfo[2],
                    } )
                });
            }

            // callback pre Action
            if ( typeof this.diceSelectionArgs.onConfirm === "function" )
                this[ this.diceSelectionArgs.onConfirm ]( result );

            if (!dojo.hasClass('btn-show-chooser', 'hide')) {
                dojo.addClass('btn-show-chooser', 'hide');
            }
            // callback on Action
            this[ this.diceSelectionArgs.action ]( result );
        },

        getSpanQuantityDiceSelection: function()
        {
            var qty = 0;
            dojo.query("#dice-selector .selected .num").forEach( function(element) {
                var text = element.innerText || element.textContent;
                text = text == "" ? 0 : parseInt( text );
                qty += text;
            });
            return qty;
        },

        onChangeMazeOpacity: function(event)
        {
            dojo.stopEvent(event);

            var $mazeOpacityRange = document.getElementById('maze-opacity');
            var $mazeBoard        = document.getElementById('maze-board');
            var mazeOpacityValue = $mazeOpacityRange.value;

            document.cookie = 'dfMazeOpacity=' + mazeOpacityValue;
            $mazeBoard.className = '';
            $mazeBoard.classList.add('opacity-' + (mazeOpacityValue * 10));
        },

        onClickMazePulse: function(event)
        {
            dojo.stopEvent(event);

            var $mazeBoard = document.getElementById('maze-board');
            $mazeBoard.classList.toggle('pulse');
            var mazePulseValue = $mazeBoard.classList.contains('pulse');

            document.cookie = 'dfMazePulse=' + mazePulseValue;
        },

        ///////////////////////////////////////////////////
        //// Reaction to cometD notifications

        setupNotifications: function()
        {
            // console.log( 'notifications subscriptions setup' );

            dojo.subscribe("notifBlessing", this, "notifBlessing");
            dojo.subscribe("notifBeginTurn", this, "notifBeginTurn");
            dojo.subscribe("notifLastTurn", this, "notifLastTurn");
            dojo.subscribe("notifEndTurn", this, "notifEndTurn");
            dojo.subscribe("notifBeginPlayerTurn", this, "notifBeginPlayerTurn");
            dojo.subscribe("notifSecondAction", this, "notifSecondAction");
            dojo.subscribe("updateCounters", this, "notifUpdateCounters");
            this.notifqueue.setSynchronous("notifBlessing", 500);
            dojo.subscribe("notifSideForged", this, "notifSideForged");
            this.notifqueue.setSynchronous("notifSideForged", 2000);
            dojo.subscribe("notifMovePawn", this, "notifMovePawn");
            this.notifqueue.setSynchronous("notifMovePawn", 1000);
            dojo.subscribe("notifExploitBuy", this, "notifBuyExploit");
            this.notifqueue.setSynchronous("notifExploitBuy", 750);
            dojo.subscribe("notifOusting", this, "notifOusting");
            this.notifqueue.setSynchronous("notifOusting", 1000);
            dojo.subscribe("notifAddReinforcement", this, "notifAddReinforcement");
            dojo.subscribe("notifAddToken", this, "notifAddToken");
            dojo.subscribe("notifAddTokenScepter", this, "notifAddTokenScepter");
            dojo.subscribe("notifHammerVP", this, "notifHammerVP");
            dojo.subscribe("notifRemoveHammer", this, "notifRemoveHammer");
            dojo.subscribe("notifBeginScoring", this, "doNothing");
            this.notifqueue.setSynchronous("notifBeginScoring", 2500);
            // delay should a bit more than .play-area transition
            dojo.subscribe("notifEndScoring", this, "notifEndScoring");
            this.notifqueue.setSynchronous("notifEndScoring", 1000);
            dojo.subscribe("notifEndScoringTitan", this, "notifEndScoringTitan");
            this.notifqueue.setSynchronous("notifEndScoringTitan", 1000);
            dojo.subscribe("notifUseCerberusToken", this, "notifUseCerberusToken");
            //dojo.subscribe("notifResetScepter", this, "notifResetScepter");

            dojo.subscribe("notifUseScepter", this, "notifUseScepter");
            dojo.subscribe("notifUseTritonToken", this, "notifUseTritonToken");
            dojo.subscribe("notifDraft", this, "notifDraft");
            dojo.subscribe("notifAutoHammer", this, "notifAutoHammer");
            dojo.subscribe("notifDiceSwitch", this, "notifDiceSwitch");
            this.notifqueue.setSynchronous("notifDiceSwitch", 3000);
            dojo.subscribe("notifPauseDice", this, "doNothing");
            this.notifqueue.setSynchronous("notifPauseDice", 1500);
            dojo.subscribe("notifCompanion", this, "notifCompanion");
            dojo.subscribe("notifUseCompanion", this, "notifUseCompanion");
            dojo.subscribe("notifCelestialRoll", this, "notifCelestialRoll");
            // maze management
            dojo.subscribe("notifMazeMove", this, "notifMazeMove");
            dojo.subscribe("notifMazeTreasure", this, "notifMazeTreasure");
            dojo.subscribe("twinUpdate", this, "notifTwinUpdate");
            dojo.subscribe("notifResetTwins", this, "notifResetTwins");
            // titan management
            dojo.subscribe("notifTitanMove", this, "notifTitanMove");
            dojo.subscribe("notifMemorySetup", this, "notifMemorySetup");
            dojo.subscribe("notifRemoveMemoryToken", this, "notifRemoveMemoryToken");

        },

        notifTwinUpdate: function(notif)
        {
            dojo.query('#token-' + notif.args.player_id + '-' + notif.args.card_id).addClass('unusable');
        },

        notifResetTwins: function(notif)
        {
            dojo.query('.ressources-effect-twins.unusable').removeClass('unusable');
        },

        doNothing: function(notif) {},

        notifUpdateCounters: function (notif)
        {
            //console.log('notifUpdateCounters', notif);

            // Hack for updating scepters...
            for (var arg in notif.args) {
                if (arg.match(/countscepter_([0-9]+)/)) {
                    var value = notif.args[arg].counter_value;
                    $scepter = document.getElementById(arg).parentNode;

                    $scepter.classList.remove('token-scepter-0', 'token-scepter-1', 'token-scepter-2', 'token-scepter-3', 'token-scepter-4', 'token-scepter-5', 'token-scepter-6');
                    $scepter.classList.add('token-scepter-' + value);

                    if (document.querySelector('.current-player-play-area #' + arg) != null
                        && this.isCurrentPlayerActive()
                    ) {
                        if (value >= 4) {
                            $scepter.classList.add('clickable');
                        } else {
                            $scepter.classList.remove('clickable');
                        }
                    }
                }
            }

            this.updateCounters(notif.args);
        },

        notifBlessing: function(notif)
        {
            var dice1     = notif.args.dice1;
            var dice2     = notif.args.dice2;
            var player_id = notif.args.player_id;

            if (notif.args.roll) {
                if (dice1)
                    this.rollDice( player_id, 1, dice1 );
                if (dice2)
                    this.rollDice( player_id, 2, dice2 );
            }
            // delay then ressource move
        },

        notifCelestialRoll: function (notif)
        {
            this.rollCelestialDice(notif.args.sideCelestial);
        },

        notifUseCompanion: function (notif)
        {
            this.notifCompanion({args : {card_id : 'power-' + notif.args.card, val : 9999}});
        },

        notifAutoHammer: function(notif)
        {
            var btnEl = document.getElementById('btn-auto-hammer');
            var enabled = notif.args.done == 'enable' ? 1 : 0;
            dojo.attr(btnEl, 'data-enabled', enabled);
            btnEl.innerHTML = enabled == 0 ? this.translatableTexts.autoHammerEnableButton : this.translatableTexts.autoHammerDisableButton;
        },

        notifDiceSwitch: function(notif)
        {
            for (var target_player in notif.args.playerSwitch) {
                if ( target_player == 0 )
                    continue;

                from_player = notif.args.playerSwitch[ target_player ];
                this.moveDices( from_player, target_player );
            }
        },

        moveDices: function( from_player, target_player ) {
            var self = this;
            var el1 = dojo.query('#player-container-' + from_player + ' #player-' + from_player + '-dice-3D-1')[0];
            var el2 = dojo.query('#player-container-' + from_player + ' #player-' + from_player + '-dice-3D-2')[0];

            // prevent dices from rolling before moving
            this.prepareDice( el1.parentElement );
            this.prepareDice( el2.parentElement );

            // slide to players boards
            this.slideToPlus( el1.id, 'dice1-result-player-' + target_player, 0, function() {
                // callback
                self.refreshDice( target_player, 1 );
            } ).play();

            this.slideToPlus( el2.id, 'dice2-result-player-' + target_player, 0, function() {
                // callback
                self.refreshDice( target_player, 2 );
            } ).play();
        },

        refreshDice: function ( player_id, dice_num )
        {
            var self = this;

            var $el = document.querySelector('#dice' + dice_num + '-result-player-' + player_id + ' .dice');
            $el.id = 'player-' + player_id + '-dice-3D-' + dice_num;
            // {0..6} .id .class
            // get dice 3D element
            var $elSides = Array.from($el.querySelectorAll('.bside')); // array of divs

            // for each side, get ID and class not .bside
            var dice = [];

            for (var index in $elSides) {
                if ( !this.isNumber(index) )
                    break;

                var $elSide  = $elSides[ index ];
                var classes = $elSide.className.split(' ');
                var elSideClass = '';
                for ( var i in classes ) {
                    if (classes[ i ] != 'bside') {
                        elSideClass = classes[ i ];
                    }
                }

                var sideId = $elSide.id.match(/side_([0-9]+)/)[1];
                var type = dice_num == 1 ? 'fire' : 'moon';
                dice[ index ] = {
                    'id'        : sideId,
                    'class'     : elSideClass,
                    'type'      : Array.from(this.sideClass).indexOf(elSideClass),
                };
            }

            dice.dice      = dice_num;
            dice.player_id = player_id;
            dice.type      = type;

            var flatContainerId = 'player-' + player_id + '-dice-' + dice_num;
            dojo.empty(flatContainerId);
            dojo.place(this.format_block('jstpl_dice_flat', dice), flatContainerId);

            var el = dojo.query('#dice' + dice_num + '-result-player-' + player_id + ' .dice')[0];
            dojo.empty(el);
            dojo.place(this.format_block('jstpl_dice', dice), el);
        },

        notifDraft: function(notif)
        {
            var slot = notif.args.slot;

            // place cards, update gamedatas
            for(var i = Object.keys(notif.args.exploit).length - 1; i >= 0; i--)
            {
                var cardId = Object.keys(notif.args.exploit)[i];
                var cardObject = notif.args.exploit[ cardId ];
                if ( this.gamedatas.exploits[ slot ] == undefined )
                    this.gamedatas.exploits[ slot ] = {};

                this.gamedatas.exploits[ slot ][ cardId ] = cardObject;
                this.exploits[ slot ].addItemType(slot, 0, g_gamethemeurl + 'img/sprite-cards-reb.jpg', this.exploitSpritePosition[ cardObject.type ] * 2);
                this.exploits[ slot ].addToStockWithId( slot, cardId );
            }

            if (notif.args.exploitName == 'celestial')
                dojo.removeClass("celestial_dice", "hide");

            // update card number
            if (this.gamedatas.exploits.hasOwnProperty( slot )) {
                var nbExploitsLeft = Object.keys( this.gamedatas.exploits[ slot ] ).length;
                dojo.place(this.format_block('jstpl_card_number', {
                    'slot' : slot,
                    'nb'   : nbExploitsLeft
                }), 'exploit-' + slot, 'first');
            }
        },

        notifBeginTurn: function(notif) {
            document.getElementById("turncount").className = "turn" + notif.args.turn;
            document.getElementById("current-turn-number").innerHTML = notif.args.turn;
        },

        notifLastTurn: function(notif) {
            var el = document.getElementById('nb-turns-container');
            dojo.addClass(el, 'lastTurn');
            el.innerHTML = this.translatableTexts.lastTurnMessage;
        },

        notifEndTurn: function(notif) {
            // reactivate powers
            dojo.query(".powers-small").removeClass("used");
        },

        notifBeginPlayerTurn: function (notif){
            this.hideDraftBoard();

            // disable Triton token of previous active user
            this.deactivateTritonToken();
            // reactivate powers
            dojo.query(".powers-small").removeClass("used");
            if ( dojo.query(".player-board.active").length != 0 )
                dojo.removeClass( dojo.query(".player-board.active")[0], 'active' );

            dojo.addClass( 'overall_player_board_' + notif.args.player_id, 'active' );

            if ( dojo.query(".play-area.active").length != 0 )
                dojo.removeClass( dojo.query(".play-area.active")[0], 'active' );

            dojo.addClass( 'player-container-' + notif.args.player_id, 'active' );
            this.turnPlayerId = notif.args.player_id;
            // enable triton token of active user
            this.activateTritonToken();
        },

        notifSecondAction: function (notif){
            dojo.removeClass('action_p' + this.getActivePlayerId(), 'hide');
        },

        notifOusting: function (notif) {
            this.slideToObject('player_' + this.colors[notif.args.ousted_player], 'position-init-' + this.colors[notif.args.ousted_player]).play();
        },

        notifSideForged: function(notif) {
            // do forge (slide sides)
            this.doForge( notif.args.player_id, notif.args.old_side, notif.args.side, notif.args.side_type_name );
        },

        notifMemorySetup: function(notif) {
            var self             = this;
            var token_split = notif.args.token.split('_');
            var side = "";
            let ressource = "";
            //console.debug(notif.args);

            if (notif.args.side == '1') {
                side = 'sun';
                ressource = '2 [L] 1 [FS]';
            }
            else {
                side = 'moon';
                ressource = '2 [AS] 1 [MS]';
            }

            dojo.place(
                    this.format_block('jstpl_memory_id', {
                        'id'   :  notif.args.token,
                        'type' : this.memoryMap[token_split[0]] + side,
                    }), 'pile-' + notif.args.player_id
                );

                this.slideToPlus( notif.args.token, 'memory-' + notif.args.island, 0, function() {
                } ).play();

            token_owner = '<span style="font-weight:bold;color:#' + this.gamedatas.players[notif.args.player_id]['color'] + '">' + this.gamedatas.players[notif.args.player_id]['name'] + '</span>';

            this.addTooltipHtml(notif.args.token, this.format_block('jstpl_tooltip_title', {
                        'title': _('Memory token of ') + token_owner,
                        'description' : _('Gain ') + this.ressourcesTextToIcon(ressource)}));
        },

        notifRemoveMemoryToken: function (notif) {
            this.removeTooltip(notif.args.tokenId);
            dojo.destroy(notif.args.tokenId);
        },

        notifBuyExploit: function (notif) {
            var self             = this;
            var dest             = notif.args.pile;
            var player_id        = notif.args.player_id

            this.removeTooltip('exploit-' + notif.args.card_pos + '_item_' + notif.args.card_id);

            if (dest.indexOf('table') == -1) {
                this.exploits[dest].addToStockWithId(notif.args.card_type, notif.args.card_id, 'exploit-' + notif.args.card_pos + '_item_' + notif.args.card_id);
                this.exploits[notif.args.card_pos].removeFromStockById(notif.args.card_id);
            }
            else {
                this.exploits['pile-' + player_id].addToStockWithId(notif.args.card_type, notif.args.card_id, 'exploit-' + notif.args.card_pos + '_item_' + notif.args.card_id);
                this.exploits[notif.args.card_pos].removeFromStockById(notif.args.card_id);
            }

            if (notif.args.card_type == 'chest') {
                document.getElementById("gold_max_p" + player_id).innerHTML =  parseInt(document.getElementById("gold_max_p" + player_id).innerHTML) + 4;
                document.getElementById("fire_max_p" + player_id).innerHTML =  parseInt(document.getElementById("fire_max_p" + player_id).innerHTML) + 3;
                document.getElementById("moon_max_p" + player_id).innerHTML =  parseInt(document.getElementById("moon_max_p" + player_id).innerHTML) + 3;
            } else if (notif.args.card_type == 'hammer') {
                var node = dojo.byId('hammer_container_p' + player_id);

                this.gamedatas.players[player_id].remainingHammer++;
                $elHammerLeft = dojo.query('#hammersleft_p' + player_id)[0];
                $elHammerLeft.innerHTML = this.gamedatas.players[player_id].remainingHammer;

                // if we have no hammer yet
                if ( dojo.hasClass(node, "hide") ) {
                    dojo.removeClass( node, 'hide' );
                    this.gamedatas.counters[ 'hammercount_p' + player_id ] = {'counter_name': 'hammercount_p' + player_id, 'counter_value' : '0'};
                    dojo.addClass( 'hammer_p' + player_id, 'ressources-hammer1' );
                    dojo.query('#hammercount_p' + player_id)[0].innerHTML = 0;
                    dojo.query('#hammers_p' + player_id)[0].innerHTML = 1;
                } else {
                    dojo.removeClass( $elHammerLeft, 'hide' );
                }

                dojo.place(
                    this.format_block('jstpl_ressource_id', {
                        'id'   : 'hammer_ptemp',
                        'size' : 'small',
                        'type' : 'hammer'
                    }), 'exploit-M1'
                );

                this.slideToPlus( 'hammer_ptemp', 'hammer_container_p' + player_id, 0, function() {
                    dojo.destroy('hammer_ptemp');
                } ).play();
            }
            else if ( this.classEffect[ notif.args.card_type ] != undefined ) {
                var elId = 'token-' + player_id + '-' + notif.args.card_id;
                dojo.place(
                    this.format_block('jstpl_ressource_id', {
                        'id'   : elId,
                        'size' : 'small',
                        'type' : this.classEffect[ notif.args.card_type ]
                    }),
                    'action_p' + player_id, 'before'
                );

                this.addPowerToolTip( elId, notif.args.card_type );
            }

            // decrease card amount
            var el = dojo.byId('card-counter-' + notif.args.card_pos);
            if ( nb = this.exploits[notif.args.card_pos].count() ) {
                el.innerHTML = this.exploits[notif.args.card_pos].count();
            } else {
                dojo.destroy( el );
            }
        },

        notifMovePawn: function (notif) {
            // move player pawn in position
            if (notif.args.island == 'init')
                position = 'position-init-' + this.colors[notif.args.player_color];
            else
                position = 'position-' + notif.args.island;
            this.slideToObject('player_' + this.colors[notif.args.player_color], position).play();
        },

        notifMazeMove: function (notif)
        {
            this.slideToPlus(this.colors[notif.args.player_color] + '-golem', 'maze-tile-' + notif.args.position).play();
        },

        notifTitanMove: function (notif)
        {
            this.slideToPlus(this.colors[notif.args.player_color] + '-player', 'titan-tile-' + notif.args.position).play();
        },

        notifMazeTreasure: function (notif)
        {
            //console.log('notifMazeTreasure', notif);
            this.addTreasureToolTip(notif.args.position, notif.args.treasure);
        },

        notifAddReinforcement: function (notif)
        {
            if (notif.args.power == 'merchant') {
                $merchant = document.querySelector("#powers_p" + notif.args.player_id + " .power-merchant");

                if ($merchant != null) {
                    $merchant.innerHTML = parseInt($merchant.innerHTML) + 1;

                    return;
                }
            }


            dojo.place( dojo.place( this.format_block('jstpl_power', {
                'id'   : notif.args.card_id,
                'type' : notif.args.power,
            }), 'powers_p' + notif.args.player_id ) ,'powers_p' + notif.args.player_id);

            this.addPowerToolTip( 'power-' + notif.args.card_id, notif.args.power );

            if (notif.args.power == 'merchant') {
                $merchant = document.querySelector("#powers_p" + notif.args.player_id + " .power-merchant");
                $merchant.innerHTML = 1;
            }
        },

        notifAddToken: function (notif)
        {
            dojo.place( dojo.place( this.format_block('jstpl_token_id', {
                'size'      : 'small',
                'type'      : notif.args.token,
                'player_id' : notif.args.player_id,
                'num'       : notif.args.card_id,
            }), 'tokens_p' + notif.args.player_id ),'tokens_p' + notif.args.player_id);

            this.addPowerToolTip('token-' + notif.args.token + '-' + notif.args.player_id + '-' + notif.args.card_id, notif.args.token);
            this.activateTritonToken();
        },

        notifAddTokenScepter: function (notif)
        {
            var $el = dojo.place( dojo.place( this.format_block('jstpl_token_scepter', {
                'size'      : 'small',
                'type'      : notif.args.token,
                'player_id' : notif.args.player_id,
                'num'       : notif.args.card_id,
            }), 'tokens_p' + notif.args.player_id ),'tokens_p' + notif.args.player_id);

            $el.classList.add('token-' + notif.args.token + '-0');

            this.addPowerToolTip('token-' + notif.args.token + '-' + notif.args.player_id + '-' + notif.args.card_id, notif.args.token);
            this.gamedatas.counters[ 'countscepter_' + notif.args.card_id ] = {'counter_name': 'countscepter_' + notif.args.card_id, 'counter_value' : '0'};
            this.activateTritonToken();
        },

        notifHammerVP: function (notif)
        {
            var player_id = notif.args.player_id;

            dojo.removeClass('hammer_p' + player_id, 'ressources-hammer' + notif.args.hammer_phase);

            if (notif.args.hammer_phase == 1){
                dojo.addClass('hammer_p' + player_id, 'ressources-hammer2');
            } else {
                dojo.addClass('hammer_p' + player_id, 'ressources-hammer1');
                this.gamedatas.players[player_id].remainingHammer--;
            }

            $elHammerLeft = dojo.query('#hammersleft_p' + player_id)[0];
            $elHammerLeft.innerHTML = this.gamedatas.players[player_id].remainingHammer;

            if (this.gamedatas.players[player_id].remainingHammer <= 1)
                dojo.addClass('hammersleft_p' + player_id, 'hide');
        },

        notifRemoveHammer: function (notif)
        {
            dojo.removeClass('hammer_container_p' + notif.args.player_id, 'ressources-hammer2');
            dojo.addClass('hammer_container_p' + notif.args.player_id, 'hide');
        },

        notifUseCerberusToken: function(notif)
        {
            var el = dojo.query('.token-cerberus', 'tokens_p' + notif.args.player_id)[0];
            dojo.destroy(el);
        },

        notifUseScepter: function(notif)
        {
            //console.debug(notif);
            this.updateScepter('moon', notif.args.player_id, notif.args.moonshard);
            this.updateScepter('fire', notif.args.player_id, notif.args.fireshard);
        },

        updateScepter(resource, player_id, amount) {
            var el = dojo.query('#scepter_' + resource + '_' + player_id) [0];
            // if for any reasons element does not exist
            if (!el) {
                return ;
            }

            if (amount != 0){
                dojo.removeClass(el, 'hide');
                el.innerHTML = '(+' + amount + ')';
            }
            else {
                dojo.addClass(el, 'hide');
                el.innerHTML = '';
            }
        },

        notifUseTritonToken: function(notif)
        {
            var el = dojo.query('.token-triton', 'tokens_p' + notif.args.player_id)[0];
            dojo.destroy(el);
        },

        notifEndScoring: function (notif)
        {
            var nom_container = 'final-card-p' + notif.args.player_id;
            this.scoreCtrl[ notif.args.player_id ].incValue( notif.args.vp );
            this.exploits[ nom_container ].addToStockWithId( notif.args.card_type, notif.args.card_id, notif.args.pile + "_item_" + notif.args.card_id );
            this.exploits[ notif.args.pile ].removeFromStockById( notif.args.card_id );
        },

        notifEndScoringTitan: function (notif)
        {
            this.scoreCtrl[ notif.args.player_id ].incValue( notif.args.vp );
        },

        notifCompanion: function (notif)
        {
            if (notif.args.val == 9999)
                dojo.query("#" + notif.args.card_id).addClass('hide');
            else {
                dojo.query("#" + notif.args.card_id).removeClass('power-companion-' + parseInt(notif.args.val - 1));
                dojo.query("#" + notif.args.card_id).addClass('power-companion-' + parseInt(notif.args.val));
            }
        },

        hideDraftBoard: function()
        {
            if ( dojo.query("#draft-container.hide").length != 0 )
                return;
            dojo.query("#draft-container").addClass('hide');
            dojo.query(".container-play-area").removeClass('hide');
        },

        rollDice: function(player_id, dice, side) {
            var elDice = document.getElementById("player-" + player_id + "-dice-3D-" + dice).parentElement;

            if (typeof side === "number")
            {
                var sideUpClass = "sideup" + side;
            }
            else if (side != undefined)
            {
                var elSide      = ( elDice.getElementsByClassName( this.sideClass[ side ] ).length ) ? elDice.getElementsByClassName( this.sideClass[ side ] )[0] : false;
                var sideUpClass = "sideup" + elSide.parentElement.getAttribute("data-numside");
                var sideId      = elSide.id.match(/side_([0-9]+)/)[1];
                this.lastSideUp[ player_id ][ dice ] = {
                    'id'   : sideId,
                    'type' : side
                };
            }
            else
            {
                var sideUpClass = "sideup" + ( Math.floor(Math.random() * 6 ) + 1 );
            }

            this.prepareDice( elDice );

            elDice.classList.add("roll");
            elDice.classList.add( sideUpClass );
        },

        rollCelestialDice: function(side, roll)
        {
            var elDice = document.getElementById("celestial_dice").parentElement;
            roll = typeof roll !== 'undefined' ? roll : true;

            if (side != undefined)
            {
                var elSide      = ( elDice.getElementsByClassName( this.sideClass[ side ] ).length ) ? elDice.getElementsByClassName( this.sideClass[ side ] )[0] : false;
                //console.debug(elSide);
                var sideUpClass = "sideup" + elSide.parentElement.getAttribute("data-numside");
                //var sideId      = elSide.id.match(/side_([0-9]+)/)[1];
            }
            else
            {
                var sideUpClass = "sideup" + ( Math.floor(Math.random() * 6 ) + 1 );
            }

            this.prepareDice(elDice);

            if (roll)
                elDice.classList.add("roll");
            elDice.classList.add( sideUpClass );
        },

        prepareDice: function(el)
        {
            // var list = ["roll", "sideup1", "sideup2", "sideup3", "sideup4", "sideup5", "sideup6"]; ...list
            el.classList.remove( "roll", "sideup1", "sideup2", "sideup3", "sideup4", "sideup5", "sideup6" );
            // https://css-tricks.com/restart-css-animation/
            void el.offsetWidth;
        },

        slideToPlus: function( el_id, el_destination, anim_delay, callback )
        {
            anim_delay = ( anim_delay == undefined ) ? 0 : anim_delay;
            this.attachToNewParent( el_id, el_destination );

            var args = {
                node: el_id,
                top: 0,
                left: 0,
                unit: 'px',
                duration : 1500,
                delay : anim_delay,
            }

            if ( callback != undefined && typeof callback === 'function' )
                args.onEnd = callback;

            return dojo.fx.slideTo( args );
        },

        // DEBUG TO REMOVE
        debugResourcesAll: function()
        {
            this.ajaxcall('/diceforge/diceforge/debugResourcesAll.html', {lock: true, }, this, function( result ) {}, function( is_error ) { } );
        },

        // return first letter of string in CAPS
        capitalize: function(str) {
            return str.charAt(0).toUpperCase() + str.slice(1);
        },

        // make a copy of an object (not a reference)
        duplicateObject: function(obj) {
            return JSON.parse(JSON.stringify(obj));
        },

        getKeys: function(obj){
           var keys = [];
           for(var key in obj){
              keys.push(key);
           }
           return keys;
        },

        swapKeys: function(obj){
            var ret = {};
            for(var key in obj){
                ret[obj[key]] = key;
            }
            return ret;
        },

        isEmptyObject: function(obj) {
            for(var prop in obj) {
                if (Object.prototype.hasOwnProperty.call(obj, prop)) {
                    return false;
                }
            }
            return true;
        },

        isNumber: function(n) {
          return !isNaN(parseFloat(n)) && isFinite(n);
        },

        getMyCookie: function(name) {
            var myCookie = document.cookie.split(';');
            if (myCookie.length == 0) {
                return null;
            }

            for (var i in myCookie) {
                var params = myCookie[i].trim();
                [param, value] = params.split('=');

                if (param == name) {
                    return value;
                }
            }

            return null;
        },

        /**
         * Represents a search trough an array.
         * @function search
         * @param {Array} array - The array you wanna search trough
         * @param {string} key - The key to search for
         * @param {string} [prop] - The property name to find it in
         */
        search: function (array, key, prop){
            // Optional, but fallback to key['name'] if not selected
            prop = (typeof prop === 'undefined') ? 'name' : prop;

            for (var i=0; i < array.length; i++) {
                if (array[i][prop] === key) {
                    return array[i];
                }
            }
        },

        pluck: function(array, key) {
            return array.map(function(obj) {
                return obj[key];
            });
        }
   });
});
