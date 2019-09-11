<?php
/**
 * @copyright Copyright (c) 2019 Robin Appelman <robin@icewind.nl>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\DocumentServer\AppInfo;

use OCA\DocumentServer\IPC\IIPCFactory;
use OCA\DocumentServer\IPC\IPCFactory;
use OCA\DocumentServer\IPC\MemcacheIPCFactory;
use OCA\DocumentServer\IPC\RedisIPCFactory;
use OCA\DocumentServer\OnlyOffice\URLDecoder;
use OCA\Onlyoffice\AppConfig;
use OCA\Onlyoffice\Crypt;
use OCP\AppFramework\App;
use OCP\AppFramework\IAppContainer;

class Application extends App {
	public function __construct(array $urlParams = []) {
		parent::__construct('documentserver', $urlParams);

		$container = $this->getContainer();

		$container->registerService(IIPCFactory::class, function (IAppContainer $c) {
			$factory = new IPCFactory();
			$factory->registerBackend($c->query(MemcacheIPCFactory::class));
			$factory->registerBackend($c->query(RedisIPCFactory::class));

			return $factory;
		});

		$container->registerService(URLDecoder::class, function (IAppContainer $container) {
			$server = $container->getServer();
			$appConfig = new AppConfig('onlyoffice');
			$crypto = new Crypt($appConfig);

			return new URLDecoder(
				$crypto,
				$server->getUserSession(),
				$server->getShareManager(),
				$server->getRootFolder()
			);
		});
	}

	public function register() {

	}
}
