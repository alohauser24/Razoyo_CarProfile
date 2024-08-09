<?php
namespace Razoyo\CarProfile\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\UrlInterface;

class Save extends Action
{
    protected $customerSession;
    protected $customerRepository;
    protected $dataPersistor;
    protected $logger;
    protected $resourceConnection;
    protected $resultPageFactory;
    protected $urlBuilder;

    public function __construct(
        Context $context,
        Session $customerSession,
        CustomerRepositoryInterface $customerRepository,
        DataPersistorInterface $dataPersistor,
        LoggerInterface $logger,
        ResourceConnection $resourceConnection,
        PageFactory $resultPageFactory,
        UrlInterface $urlBuilder
    ) {
        $this->customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
        $this->dataPersistor = $dataPersistor;
        $this->logger = $logger;
        $this->resourceConnection = $resourceConnection;
        $this->resultPageFactory = $resultPageFactory;
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context);
    }

    public function execute()
    {
        $carId = $this->getRequest()->getParam('car_id');
        if (!$carId) {
            $this->messageManager->addErrorMessage(__('Please Select a Car Model'));
            $this->_redirect('carprofile/index/index');
            return;
        }

        try {
            $customerId = $this->customerSession->getCustomerId();
            if (!$customerId) {
                throw new \Exception('Save.php: Customer is not logged in.');
            }

            $connection = $this->resourceConnection->getConnection();
            $tableName = $connection->getTableName('customer_entity_text');

            // Get attribute ID for 'car_profile'
            $attributeId = $connection->fetchOne(
                "SELECT attribute_id FROM eav_attribute WHERE attribute_code = 'car_profile' AND entity_type_id = (SELECT entity_type_id FROM eav_entity_type WHERE entity_type_code = 'customer')"
            );

            if (!$attributeId) {
                throw new \Exception('Attribute ID for "car_profile" not found.');
            }

            // Check if a record already exists
            $existingRecord = $connection->fetchRow(
                $connection->select()
                    ->from($tableName)
                    ->where('entity_id = ?', $customerId)
                    ->where('attribute_id = ?', $attributeId)
            );

            if ($existingRecord) {
                // Update existing record
                $connection->update(
                    $tableName,
                    ['value' => $carId],
                    ['entity_id = ?' => $customerId, 'attribute_id = ?' => $attributeId]
                );
            } else {
                // Insert new record
                $connection->insert(
                    $tableName,
                    [
                        'entity_id' => $customerId,
                        'attribute_id' => $attributeId,
                        'value' => $carId
                    ]
                );
            }

            // Log the customer data being saved
            $this->logger->info('Saving car profile for customer ID ' . $customerId . ' with car ID: ' . $carId);

            $this->messageManager->addSuccessMessage(__('Your car profile has been updated.'));
        } catch (\Exception $e) {
            $this->logger->error('Error updating car profile: ' . $e->getMessage());
            $this->messageManager->addErrorMessage(__('An error occurred while updating your car profile: %1', $e->getMessage()));
        }

        $this->_redirect('carprofile/index/index');

    }
    
}
