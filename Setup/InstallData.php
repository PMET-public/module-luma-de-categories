<?php

namespace MagentoEse\LumaDECategories\Setup;

use Magento\Framework\App\State;
use Magento\Framework\File\Csv;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\SampleData\Context;
use Magento\Framework\Setup\SampleData\FixtureManager;
use Magento\Indexer\Model\Processor;
use Magento\Store\Model\Store;

    /**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{

    /**
     * 
     * @var \Magento\Framework\Setup\SampleData\Context
     */
    protected $sampleDataContext;

    /**
     * 
     * @var Store
     */
    protected $storeView;

    /**
     * 
     * @var Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $categoryCollection;

    /**
     * 
     * @var array
     */
    protected $config;

    /**
     * 
     * @var FixtureManager
     */
    protected $fixtureManager;

    /**
     * 
     * @var Csv
     */
    protected $csvReader;

    /**
     * 
     * @var Processor
     */
    protected $index;


    /**
     * 
     * @param Context $sampleDataContext 
     * @param Store $storeView 
     * @param Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollection 
     * @param State $state 
     * @param Processor $index 
     * @return void 
     */
    public function __construct(\Magento\Framework\Setup\SampleData\Context $sampleDataContext,
                                \Magento\Store\Model\Store $storeView,
                                \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollection,
                                \Magento\Framework\App\State $state,
                                \Magento\Indexer\Model\Processor $index)
    {
        try{
            $state->setAreaCode('adminhtml');
        }
        catch(\Magento\Framework\Exception\LocalizedException $e){
            // left empty
        }

        $this->config = require 'Config.php';
        $this->fixtureManager = $sampleDataContext->getFixtureManager();
        $this->csvReader = $sampleDataContext->getCsvReader();
        $this->storeView = $storeView;
        $this->categoryCollection = $categoryCollection;
        $this->index = $index;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        //Need to reindex to make sure the 2nd store index tables exist before saving products.
        //$this->index->reindexAll();

        //get view id from view code
        $_viewId = $this->storeView->load($this->config['viewCode'])->getStoreId();

        //get category label translations
        $_fileName = $this->fixtureManager->getFixture('MagentoEse_LumaDECategories::fixtures/CategoryLabels.csv');
        $_categoryLabels = $this->csvReader->getData($_fileName);

        //get categories
        $_categories = $this->categoryCollection->create()->addAttributeToSelect('*');
        foreach ($_categories as $_category) {
            $_toTranslate = $_category->getName();
            foreach($_categoryLabels as $_translation){
                if($_translation[0]==$_toTranslate){
                    $_category->setName($_translation[1]);
                    $_category->setStoreId($_viewId);
                    $_category->save();
                    continue;
                }
            }

        }
    }
}
