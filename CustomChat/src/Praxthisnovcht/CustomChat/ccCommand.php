<?php

namespace Praxthisnovcht\CustomChat;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;

/**
 * Command
 *
 */
class ccCommand {
	private $plugin;
	/**
	 *
	 * @param
	 *        	$pg
	 */
	public function __construct(ccMain $pg) {
		$this->plugin = $pg;
	}

	
	/**
	 * onCommand
	 *
	 * @param CommandSender $sender        	
	 * @param Command $command        	
	 * @param unknown $label        	
	 * @param array $args        	
	 * @return boolean
	 */
	public function onCommand(CommandSender $sender, Command $command, $label, array $args) {

		if ((strtolower ( $command->getName () ) == "CustomChat")) {
			$sender->sendMessage (TextFormat::RED . "-==[ CustomChat Info ]==-");
			$sender->sendMessage (TextFormat::RED . "Usage: /mute <player>");
			$sender->sendMessage (TextFormat::RED . "Usage: /unmute <player>");
			$sender->sendMessage (TextFormat::RED . "Usage: /delprefix <player>");
			$sender->sendMessage (TextFormat::RED . "Usage: /enablechat");	
			$sender->sendMessage (TextFormat::RED . "Usage: /disablechat");	
			$sender->sendMessage (TextFormat::RED . "Usage: /defprefix <Prefix>");
			$sender->sendMessage (TextFormat::RED . "Usage: /setprefix <Prefix> <player>");
			$sender->sendMessage (TextFormat::RED . "Usage: /setnick <Nick> <player>");
			$sender->sendMessage (TextFormat::RED . "Usage: /delnick <player>");
			return;
		}
		// disable chat for all players
		if ((strtolower ( $command->getName () ) == "disablechat")) {
			$this->plugin->getConfig ()->set ( "disablechat", true ); // config.yml
			$this->plugin->getConfig ()->save ();
			$sender->sendMessage (TextFormat::RED . "disable chat for all players" );
			$this->log ( "disable chat for all players" );
			return;
		}
		// enable chat for all players
		if ((strtolower ( $command->getName () ) == "enablechat")) {
			$this->plugin->getConfig ()->set ( "disablechat", false ); // config.yml
			$this->plugin->getConfig ()->save ();
			$sender->sendMessage (TextFormat::GREEN . "enable chat for all players" );
			$this->log ( "enable chat for all players" );
			return;
		}
		
		// sets default prefix for new players
		if ((strtolower ( $command->getName () ) == "defprefix") && isset ( $args [0] )) {
			$playerName = $args [0];
			$p = $sender->getServer ()->getPlayerExact ( $playerName );
			if ($p == null) {
				$sender->sendMessage (TextFormat::RED . "player " . $playerName . " is not online!" );
				return true;
			}
			$prefix = $args [1];
			$this->plugin->getConfig ()->set ( "default-player-prefix", $prefix );
			$this->plugin->getConfig ()->save ();
			$sender->sendMessage (TextFormat::RED . " all players default prefix set to " . $args [1] );
			return;
		}
		
		// sets prefix for player
		if ((strtolower ( $command->getName () ) == "setprefix") && isset ( $args [0] ) && isset ( $args [1] )) {
			$playerName = $args [0];
			$p = $sender->getServer ()->getPlayerExact ( $playerName );
			if ($p == null) {
				$sender->sendMessage (TextFormat::RED . "player " . $playerName . " is not online!" );
				return true;
			}
			$prefix = $args [1];
			$this->plugin->getConfig ()->set ( $p->getName ().".prefix", $prefix );
			$this->plugin->getConfig ()->save ();
			
			// $p->setDisplayName($prefix.":".$name);
			$this->plugin->formatterPlayerDisplayName ( $p );
			$sender->sendMessage (TextFormat::GREEN . $p->getName () . " prefix set to " . $args [1] );
			return;
		}
		
		// set player's prefix to default.
		if ((strtolower ( $command->getName () ) == "delprefix") && isset ( $args [0] )) {
			$playerName = $args [0];
			$p = $sender->getServer ()->getPlayerExact ( $playerName );
			if ($p == null) {
				$sender->sendMessage (TextFormat::RED . "player " . $playerName . " is not online!" );
				return true;
			}
			$this->plugin->getConfig ()->remove ( $p->getName () . ".prefix" );
			$this->plugin->getConfig ()->save ();
			$sender->sendMessage (TextFormat::RED . $p->getName () . " prefix set to default" );
			return;
		}
		
		// sets nick for player
		if ((strtolower ( $command->getName () ) == "setnick") && isset ( $args [0] ) && isset ( $args [1] )) {
			$playerName = $args [0];
			$p = $sender->getServer ()->getPlayerExact ( $playerName );
			if ($p == null) {
				$sender->sendMessage (TextFormat::RED . "player " . $playerName . " is not online!" );
				return true;
			}
			$nick = $args [1];
			$this->plugin->getConfig ()->set ( $p->getName () . ".nick", $nick );
			$this->plugin->getConfig ()->save ();
			
			$this->plugin->formatterPlayerDisplayName ( $p );
			$sender->sendMessage (TextFormat::GREEN . $p->getName () . " nick name set to " . $args [1] );
			return;
		}
		// sets nick for player
		if ((strtolower ( $command->getName () ) == "delnick") && isset ( $args [0] ) && isset ( $args [1] )) {
			$playerName = $args [0];
			$p = $sender->getServer ()->getPlayerExact ( $playerName );
			if ($p == null) {
				$sender->sendMessage (TextFormat::RED . "player " . $playerName . " is not online!" );
				return true; 
			}
			$nick = $args [1];
			$this->plugin->getConfig ()->remove ( $p->getName () . ".nick" );
			$this->plugin->getConfig ()->save ();
			// save yml
			
			$this->plugin->formatterPlayerDisplayName ( $p );
			$sender->sendMessage (TextFormat::GREEN . $p->getName () . " nick removed " );
			return;
		}
		
		// mute player from chat
		if ((strtolower ( $command->getName () ) == "mute") && isset ( $args [0] )) {
			$playerName = $args [0];
			// check if the player exist
			$p = $sender->getServer ()->getPlayerExact ( $playerName );
			if ($p == null) {
				$sender->sendMessage (TextFormat::RED . "player " . $playerName . " is not online!" );
				return true;
			}
			$perm = "chatmute";
			$p->addAttachment ( $this->plugin, $perm, true );
			$sender->sendMessage (TextFormat::GREEN . $p->getName () . " chat muted" );
			// $this->log ( "isPermissionSet " . $p->isPermissionSet ( $perm ) );
			return;
		}
		// - unmute player from chat
		if ((strtolower ( $command->getName () ) == "unmute") && isset ( $args [0] )) {
			$playerName = $args [0];
			// check if the player exist
			$p = $sender->getServer ()->getPlayerExact ( $playerName );
			if ($p == null) {
				$sender->sendMessage (TextFormat::RED . "player " . $playerName . " is not online!" );
				return true;
			}
			$perm = "chatmute";
			foreach ( $p->getEffectivePermissions () as $pm ) {
				if ($pm->getPermission () == $perm) {
					// $this->log ( "remove attachements " . $pm->getValue () );
					$p->removeAttachment ( $pm->getAttachment () );
					$sender->sendMessage (TextFormat::GREEN . $p->getName () . " chat unmuted" );
					return;
				}
			}
			$sender->sendMessage (TextFormat::RED . $p->getName () . " already unmuted" );
			// $this->log ( "isPermissionSet " . $p->isPermissionSet ( $perm ) );
			return; // next try again
			
		}
// TAGS
		// sets default tags for new players
		if ((strtolower ( $command->getName () ) == "deftags") && isset ( $args [0] )) {
			$playerName = $args [0];
			$p = $sender->getServer ()->getPlayerExact ( $playerName );
			if ($p == null) {
				$sender->sendMessage (TextFormat::RED . "player " . $playerName . " is not online!" );
				return true;
			}
			$tags = $args [1];
			$this->plugin->getConfig ()->set ( "default-player-tags", $tags );
			$this->plugin->getConfig ()->save ();
			$sender->sendMessage (TextFormat::RED . " all players default tags set to " . $args [1] );
			return;
		}
		
		// sets tags for player
		if ((strtolower ( $command->getName () ) == "tags") && isset ( $args [0] ) && isset ( $args [1] )) {
			$playerName = $args [0];
			$p = $sender->getServer ()->getPlayerExact ( $playerName );
			if ($p == null) {
				$sender->sendMessage (TextFormat::RED . "player " . $playerName . " is not online!" );
				return true;
			}
			$tags = $args [1];
			$this->plugin->getConfig ()->set ( $p->getName ().".tags", $tags );
			$this->plugin->getConfig ()->save ();
			
			// $p->setDisplayName($tags.":".$name);
			$this->plugin->formatterPlayerDisplayName ( $p );
			$sender->sendMessage (TextFormat::GREEN . $p->getName () . " tags set to " . $args [1] );
			return;
		}
		
		// set player's tags to default.
		if ((strtolower ( $command->getName () ) == "deltags") && isset ( $args [0] )) {
			$playerName = $args [0];
			$p = $sender->getServer ()->getPlayerExact ( $playerName );
			if ($p == null) {
				$sender->sendMessage (TextFormat::RED . "player " . $playerName . " is not online!" );
				return true;
			}
			$this->plugin->getConfig ()->remove ( $p->getName () . ".tags" );
			$this->plugin->getConfig ()->save ();
			$sender->sendMessage (TextFormat::RED . $p->getName () . " tags set to default" );
			return;
		}
		
	}
	             // TODO NEXT VERSION
	
	private function hasCommandAccess(CommandSender $sender) {
		if ($sender->getName () == "CONSOLE") {
			return true;
		} elseif ($sender->isOp ()) {
			return true;
		}
		return false;
	}
	
	/**
	 * Logging util function
	 *
	 * @param unknown $msg        	
	 */
	private function log($msg) {
		$this->plugin->getLogger ()->info ( $msg );
	}
}
