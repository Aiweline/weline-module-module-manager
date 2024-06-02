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
    )
    {
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
            $this->table->reset()->clearData();
            /**@var Module $module */
            $module = $event->getData('module');
            # 检查是否存在表
            try {
                $table = $model->getTable();
                /**@var Table $has */
                $has = $this->table->where($this->table::fields_name, $table)->find()->fetch();
                if ($has->getId()) {
                    throw new Exception($table . __('表已存在！该表已被：%1 模组下的 %2 模型创建，请为当前模型 %3 更换表名。', [$has->getModuleName(), $has->getModel(), $model::class]));
                }
                $this->table->reset()->clearData()
                    ->setData($this->table::fields_module_name, $module->getName())
                    ->setData($this->table::fields_name, $table, true)
                    ->setData($this->table::fields_model, $model::class)
                    ->save();
            } catch (\Exception $exception) {
//                d($exception->getMessage());
            }
        }
    }
}
