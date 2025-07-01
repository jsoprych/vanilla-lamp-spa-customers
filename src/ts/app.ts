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
    await this.loadCustomers();
    setupTabNavigation();
    setupCustomerListClickHandler((customerId: number) => this.onCustomerSelected(customerId));
    setupSearchHandler((searchTerm: string) => this.onSearch(searchTerm));
  }

  private async loadCustomers(searchTerm: string = ''): Promise<void> {
    try {
      const customers = await fetchCustomers(searchTerm);
      renderCustomerList(customers, this.currentCustomerId);
      
      if (!this.currentCustomerId && customers.length > 0) {
        await this.onCustomerSelected(customers[0].customer_id);
      }
    } catch (error) {
      console.error('Error loading customers:', error);
      alert('Failed to load customers. Please try again.');
    }
  }

  private async onCustomerSelected(customerId: number): Promise<void> {
    this.currentCustomerId = customerId;
    try {
      const [customer, contacts, addresses, activities] = await Promise.all([
        fetchCustomerDetails(customerId),
        fetchCustomerContacts(customerId),
        fetchCustomerAddresses(customerId),
        fetchCustomerActivity(customerId)
      ]);

      renderCustomerDetail(customer);
      renderCustomerContacts(contacts);
      renderCustomerAddresses(addresses);
      renderCustomerActivity(activities);
      document.querySelector('.detail-view')?.scrollTo(0, 0);
    } catch (error) {
      console.error('Error loading customer details:', error);
      alert('Failed to load customer details. Please try again.');
    }
  }

  private async onSearch(searchTerm: string): Promise<void> {
    await this.loadCustomers(searchTerm);
  }
}

document.addEventListener('DOMContentLoaded', () => {
  new CustomerApp();
});
