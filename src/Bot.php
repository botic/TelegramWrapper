<?php
namespace Telegram;

use Telegram\Commands\CommandCaller;
use Telegram\Commands\HelpCommand;
use Telegram\Commands\ICommand;

class Bot{
	/**
	 * @var Api
	 */
	private $api;

	/**
	 * @var ICommand[]
	 */
	private $commands;

	/**
	 * @param string $token
	 */
	public function __construct($token){
		$this->api = new Api($token);
		$this->commands = [];
		$this->addCommand('help', new HelpCommand());
	}

	/**
	 * @return Api
	 */
	public function getApi(){
		return $this->api;
	}


	/**
	 * @param string $name
	 * @param ICommand $command
	 */
	public function addCommand($name, $command)
	{
		if($command instanceof ICommand)
			$this->commands[$name] = $command;
	}

	/**
	 * @return ICommand[]
	 */
	public function getCommands()
	{
		return $this->commands;
	}

	/**
	 * @param bool $useWebhook
	 */
	public function work($useWebhook = false)
	{
		if($useWebhook){
			//todo Create $updates from webhook
			$updates = [];
		}else{
			$updates = $this->api->getUpdates();
		}

		$highestId = -1;

		foreach($updates as $update){
			$highestId = $update->update_id;

			echo $update->message->text . $update->message->from->id;

			if(substr($update->message->text, 0, 1) === '/'){
				$details = explode(' ', substr($update->message->text, 1));
				$command = strtolower(array_shift($details));
				$caller = new CommandCaller($this, $update->message);

				if(isset($this->commands[$command]))
					$this->commands[$command]->call($command, $details, $caller);
				elseif(isset($this->commands['help']))
					$this->commands['help']->call($command, $details, $caller);
			}
		}

		if($highestId != -1 && !$useWebhook)
			$this->api->getUpdates($highestId + 1, 1);
	}
}