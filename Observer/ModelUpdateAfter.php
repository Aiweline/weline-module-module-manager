<?php

namespace Weline\ModuleManager\Observer;

use Weline\Framework\App\Exception;
use Weline\Framework\Database\Model;
use Weline\Framework\Event\Event;
use Weline\Framework\Event\ObserverInterface;
use Weline\ModuleManager\Model\Module;
use Weline\ModuleManager\Model\Module\Table;

class ModelUpdateAfter implements ObserverInterface
{
    private Table $table;

    public function __construct(
        Table $table
    ) {
        $this->table = $table;
    }

    /**
     * @inheritDoc
     */
    public function execute(Event $event): void
    {
        $type = $event->getData('type');
        $data = $event->getData('data');
        /**@var Model $model */
        $model = $data->getModel();

        if ($model::class !== Table::class) {
            $this->table->reset();
            /**@var Module $module */
            $module = $event->getData('module');
            # 检查是否存在表
            try {
                $table = $model->getTable();
                /**@var Table $modelTable */
                $this->table->clearData()
                    ->reset()
                    ->setData($this->table::fields_module_name, $module->getName())
                    ->setData($this->table::fields_name, $table,true)
                    ->setData($this->table::fields_model, $model::class,true)
                    ->save(true);
            } catch (\Exception $exception) {
                d($exception->getMessage());
            }
        }
    }
}
