<?php

namespace skyss0fly\cavesblocks;

use skyss0fly\cavesblocks\utils\CustomConfig;
use skyss0fly\cavesblocks\utils\CustomId;
use skyss0fly\cavesblocks\EventsListener;
use pocketmine\block\Block;
use pocketmine\block\BlockBreakInfo;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockIdentifierFlattened as BIDFlattened;
use pocketmine\block\BlockLegacyIds as Ids;
use pocketmine\block\BlockIdentifier as BID;
use pocketmine\block\BlockToolType;
use pocketmine\block\Door;
use pocketmine\block\Fence;
use pocketmine\block\FenceGate;
use pocketmine\block\Opaque;
use pocketmine\block\Stair;
use pocketmine\block\StonePressurePlate;
use pocketmine\block\tile\Sign as TileSign;
use pocketmine\block\tile\TileFactory;
use pocketmine\block\utils\RecordType;
use pocketmine\block\utils\SlabType;
use pocketmine\block\utils\TreeType;
use pocketmine\block\Wall;
use pocketmine\block\WoodenPressurePlate;
use pocketmine\block\WoodenTrapdoor;
use pocketmine\inventory\CreativeInventory;
use pocketmine\item\ItemBlock;
use pocketmine\item\ItemBlockWallOrFloor;
use pocketmine\item\StringToItemParser;
use pocketmine\lang\Translatable;
use pocketmine\inventory\ArmorInventory;
use pocketmine\item\Armor;
use pocketmine\item\ArmorTypeInfo;
use pocketmine\item\Axe;
use pocketmine\item\Hoe;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemIds;
use pocketmine\item\Pickaxe;
use pocketmine\item\Shovel;
use pocketmine\item\Sword;
use pocketmine\item\Record;
use pocketmine\item\ToolTier;
use pocketmine\network\mcpe\convert\RuntimeBlockMapping;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\Config;
use ReflectionMethod;
use const pocketmine\src\BEDROCK_BLOCK_UPGRADE_SCHEMA_PATH;

class Main extends PluginBase
{

	private CustomConfig $config;
	private static self $instance;

	protected function onLoad(): void
	{
		self::$instance = $this;
		$this->saveResource("config.yml");
		$this->config = new CustomConfig(new Config($this->getDataFolder() . "config.yml", Config::YAML));
		self::initializeRuntimeIds();
		$this->initBlocks();
		$this->initTiles();
		$this->initItems();

		
	}

	protected function onEnable(): void
	{ //credits https://github.com/cladevs/VanillaX
		Server::getInstance()->getPluginManager()->registerEvents(new EventListener(), $this);
		Server::getInstance()->getAsyncPool()->addWorkerStartHook(function (int $worker): void {
			Server::getInstance()->getAsyncPool()->submitTaskToWorker(new class() extends AsyncTask {

				public function onRun(): void
				{
					Main::initializeRuntimeIds();
				}
			}, $worker);
		});
	}


	public static function getInstance(): self
	{
		return self::$instance;
	}

	public function getCustomConfig(): CustomConfig
	{
		return $this->config;
	}

	public static function initializeRuntimeIds(): void
	{
		$instance = RuntimeBlockMapping::getInstance();
		$method = new ReflectionMethod(RuntimeBlockMapping::class, "registerMapping");
		$method->setAccessible(true);

		$blockIdMap = json_decode(file_get_contents(BEDROCK_BLOCK_UPGRADE_SCHEMA_PATH . 'block_legacy_id_map.json'), true);
		$metaMap = [];

		foreach ($instance->getBedrockKnownStates() as $runtimeId => $nbt) {
			$mcpeName = $nbt->getString("name");
			$meta = isset($metaMap[$mcpeName]) ? ($metaMap[$mcpeName] + 1) : 0;
			$id = $blockIdMap[$mcpeName] ?? Ids::AIR;

			if ($id !== Ids::AIR && $meta <= 15 && !BlockFactory::getInstance()->isRegistered($id, $meta)) {
				$metaMap[$mcpeName] = $meta;
				$method->invoke($instance, $runtimeId, $id, $meta);
			}
		}
	}


	public function initBlocks(): void
	{
		$class = new \ReflectionClass(TreeType::class);
		$register = $class->getMethod('register');
		$register->setAccessible(true);
		$constructor = $class->getConstructor();
		$constructor->setAccessible(true);
		$instance = $class->newInstanceWithoutConstructor();
		$constructor->invoke($instance, 'crimson', 'Crimson', 6);
		$register->invoke(null, $instance);

		$instance = $class->newInstanceWithoutConstructor();
		$constructor->invoke($instance, 'warped', 'Warped', 7);
		$register->invoke(null, $instance);

		$cfg = $this->getCustomConfig();
		if ($cfg->isEnabledDeepslate()) {
			$this->registerBlock(new Opaque(new BID(CustomIds::DEEPSLATE_BLOCK, 0, CustomIds::DEEPSLATE_BLOCK), "Deepslate", new BlockBreakInfo(1.5, BlockToolType::PICKAXE)));
		}
	
	}

	public function initTiles(): void
	{
		$cfg = $this->getCustomConfig();
		$tf = TileFactory::getInstance();
		
	}

	public function initItems(): void
	{
		$cfg = $this->getCustomConfig();
		
		
		
		

	 function registerBlock(Block $block, bool $registerToParser = true, bool $addToCreative = true): void
	{
		BlockFactory::getInstance()->register($block, true);
		if ($addToCreative && !CreativeInventory::getInstance()->contains($block->asItem())) {
			CreativeInventory::getInstance()->add($block->asItem());
		}
		if ($registerToParser) {
			$name = strtolower($block->getName());
			$name = str_replace(" ", "_", $name);
			StringToItemParser::getInstance()->registerBlock($name, fn() => $block);
		}
	}

	 function registerItem(Item $item, bool $registerToParser = true): void
	{
		ItemFactory::getInstance()->register($item, true);
		if (!CreativeInventory::getInstance()->contains($item)) {
			CreativeInventory::getInstance()->add($item);
		}
		if ($registerToParser) {
			$name = strtolower($item->getName());
			$name = str_replace(" ", "_", $name);
			StringToItemParser::getInstance()->register($name, fn() => $item);
		}
	}

	 function registerSlab(Slab $slab) : void{
		$this->registerBlock($slab);
		$identifierFlattened = $slab->getIdInfo();
		if($identifierFlattened instanceof BIDFlattened){
			BlockFactory::getInstance()->remap($identifierFlattened->getSecondId(), $identifierFlattened->getVariant() | 0x1, $slab->setSlabType(SlabType::DOUBLE()));
		}
	}
}
}
