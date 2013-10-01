<?php
class SolrBehavior extends CActiveRecordBehavior
{

    /**
     * @var string with name of ASolrDocument sub class
     */
    public $instanceClass = 'ASolrDocument';

    /**
     * @var array maping AR fields to Solr fields
     * example:
     *  array(
     *           // ArField(s) => SolField(s)
     *           'slug' => 'slug',
     *           'title' => 'title & name',
     *           'title + text' => 'text'
     *       )
     *
     */
    public $fieldsMap = array();

    /**
     * @var string connector for "plus" in keys of array $fieldsMap
     */
    public $plusFieldsConnector = " + ";

    /**
     * @var bool update document after update record
     */
    public $onUpdate = true;

    /**
     * @param CEvent $event
     */
    public function afterSave($event)
    {
        /** @var CActiveRecord $record */
        $record = $this->getOwner();
        if(!$record->getIsNewRecord() && !$this->onUpdate) {
            return parent::afterSave($event);
        }


        $instanceClass = $this->instanceClass;
        /** @var ASolrDocument $document */
        $document = $instanceClass::model()->findByPk($record->getPrimaryKey());
        if(!$document)
            $document = new $instanceClass;

        $this->appendData($document, $record);

        $document->save();
        $this->commit();

        return parent::afterSave($event);
    }

    /**
     * @param CEvent $event
     */
    public function afterDelete($event)
    {
        /** @var CActiveRecord $record */
        $record = $this->getOwner();

        $instanceClass = $this->instanceClass;
        $document = $instanceClass::model()->findByPk($record->getPrimaryKey());
        if($document)
            $document->delete();
        $this->commit();

        return parent::afterDelete($event);
    }

    /**
     * @param ASolrDocument $document
     * @param CActiveRecord $record
     */
    private function appendData(ASolrDocument &$document, CActiveRecord $record)
    {
        $document->id = $record->getPrimaryKey();
        foreach ($this->fieldsMap as $_ar_fields => $_solr_fields) {
            $ar_fields = explode('+', $_ar_fields);
            $datas = array();
            foreach ($ar_fields as $ar_field) {
                $ar_field = trim($ar_field);
                $datas[] = $record->$ar_field;
            }
            $data = implode($this->plusFieldsConnector, $datas);

            $solr_fields = explode('&', $_solr_fields);
            foreach ($solr_fields as $solr_field) {
                $solr_field = trim($solr_field);
                $document->$solr_field = $data;
            }
        }

    }

    /**
     * Commit SolrConnection
     */
    private function commit()
    {
        $instanceClass = $this->instanceClass;
        $instanceClass::model()->getSolrConnection()->getClient()->request('<commit expungeDeletes="true" waitSearcher="false"/>');
    }
}

