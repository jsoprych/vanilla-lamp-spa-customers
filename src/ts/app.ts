import { 
    fetchCustomers, 
    fetchCustomerDetails, 
    fetchCustomerContacts, 
    fetchCustomerAddresses, 
    fetchCustomerActivity 
} from './customerService.js';
import { 
    renderCustomerList, 
    renderCustomerDetail, 
    renderCustomerContacts, 
    renderCustomerAddresses, 
    renderCustomerActivity, 
    setupTabNavigation, 
    setupCustomerListClickHandler, 
    setupSearchHandler 
} from './ui.js';

class CustomerApp {
    private currentCustomerId: number | null = null;

    constructor() {
        this.init();
    }

    private async init(): Promise<void> {
        console.log('Initializing CustomerApp');
        await this.loadCustomers();
        setupTabNavigation();
        setupCustomerListClickHandler((customerId: number) => this.onCustomerSelected(customerId));
        setupSearchHandler((searchTerm: string) => this.onSearch(searchTerm));
        console.log('CustomerApp initialized');
    }

    private async loadCustomers(searchTerm: string = ''): Promise<void> {
        console.log('Loading customers with searchTerm:', searchTerm);
        try {
            const customers = await fetchCustomers(searchTerm);
            console.log('Customers fetched successfully:', customers);
            renderCustomerList(customers, this.currentCustomerId);
            if (!this.currentCustomerId && customers.length > 0) {
                console.log('Selecting first customer, customerId:', customers[0].customer_id);
                await this.onCustomerSelected(customers[0].customer_id);
            }
        } catch (error) {
            console.error('Error loading customers:', {
                error: error.message,
                stack: error.stack,
                searchTerm: searchTerm,
                context: 'loadCustomers'
            });
            alert('Failed to load customers. Please check the console for details.');
        }
    }

    private async onCustomerSelected(customerId: number): Promise<void> {
        console.log('Customer selected, customerId:', customerId);
        this.currentCustomerId = customerId;
        try {
            console.log('Starting fetch for customer details, customerId:', customerId);
            const [customerData, contacts, addresses, activities] = await Promise.all([
                fetchCustomerDetails(customerId).then(data => {
                    console.log('Customer details fetched:', data);
                    return data[0]; // Extract the first item from the array
                }),
                fetchCustomerContacts(customerId).then(data => {
                    console.log('Contacts fetched:', data);
                    return data;
                }),
                fetchCustomerAddresses(customerId).then(data => {
                    console.log('Addresses fetched:', data);
                    return data;
                }),
                fetchCustomerActivity(customerId).then(data => {
                    console.log('Activities fetched:', data);
                    return data;
                })
            ]);
            console.log('All data fetched:', { customer: customerData, contacts, addresses, activities });
            renderCustomerDetail(customerData);
            renderCustomerContacts(contacts);
            renderCustomerAddresses(addresses);
            renderCustomerActivity(activities);
            document.querySelector('.detail-view')?.scrollTo(0, 0);
            console.log('Rendering completed for customerId:', customerId);
        } catch (error) {
            console.error('Error loading customer details:', {
                error: error.message,
                stack: error.stack,
                customerId: customerId,
                context: 'onCustomerSelected',
                requestUrl: `/api/CustomerController.php?customer_id=${customerId}`,
            });
            alert('Failed to load customer details. Please check the console for details.');
        }
    }

    private async onSearch(searchTerm: string): Promise<void> {
        console.log('Searching with term:', searchTerm);
        await this.loadCustomers(searchTerm);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM loaded, starting CustomerApp');
    new CustomerApp();
});