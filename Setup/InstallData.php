<?php

namespace MagentoEse\LumaDECategories\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;


    /**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{

    protected $sampleDataContext;
    protected $storeView;
    protected $categoryCollection;
    private $state;


    public function __construct(\Magento\Framework\Setup\SampleData\Context $sampleDataContext,
                                \Magento\Store\Model\Store $storeView,
                                \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollection,
                                \Magento\Framework\App\State $state)
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
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
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