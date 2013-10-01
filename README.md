YiiSolrBehavior Usage
===============

Require https://github.com/phpnode/YiiSolr


    public function behaviors()
    {
        return array(
            ...
            'solr' => array(
                'class' => 'ext.behaviors.SolrBehavior.SolrBehavior',
                'instanceClass' => 'ContentSolr',
                'fieldsMap' => array(
                    'slug' => 'slug',
                    'title' => 'title&name',
                    'title+text' => 'text'
                )
            )
            ...

        );

    }
