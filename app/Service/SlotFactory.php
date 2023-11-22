<?php


namespace Service;


use Admin\App;
use Service\Slot\EmptySlot;
use Service\Slot\LocalSlot;
use Service\Slot\RemoteSlot;
use Service\Slot\RemoteSshSlot;
use Service\Slot\SlotProto;

class SlotFactory
{
    public static function getSlot($id)
    {
        $slotData = Data::scope(App::DATA_SLOTS)->getById($id);
        return self::getSlotModel($slotData);
    }
    
    /**
     * @param $slotData
     *
     * @return SlotProto
     */
    public static function getSlotModel(&$slotData)
    {
        if (isset($slotData['type'])) {
            switch ($slotData['type']) {
                case SlotsPool::SLOT_TYPE_LOCAL:
                    $model = new LocalSlot();
                    break;
                case SlotsPool::SLOT_TYPE_REMOTE:
                    $model = new RemoteSshSlot();
            }
            
            $model->setData($slotData);
        } else {
            $model = new EmptySlot();    
        }
        
        $model->init();
        
        return $model;
    }
}
