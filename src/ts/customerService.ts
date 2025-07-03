// src/ts/customerService.ts
import { apiFetch } from './apiUtils.js';
import { 
    Customer, 
    CustomerContact, 
    CustomerAddress, 
    CustomerActivity 
} from './models';

// Define a type for API parameters
type ApiParams = Record<string, string>;

// Type-safe API methods
export const fetchCustomers = (searchTerm: string = ''): Promise<Customer[]> => {
    const params: ApiParams = searchTerm ? { search: searchTerm } : {};
    return apiFetch<Customer[]>('/api/CustomerController.php', params);
};

export const fetchCustomerDetails = (customerId: number): Promise<Customer> => {
    const params: ApiParams = { customer_id: customerId.toString() };
    return apiFetch<Customer>('/api/CustomerController.php', params);
};

export const fetchCustomerContacts = (customerId: number): Promise<CustomerContact[]> => {
    const params: ApiParams = { customer_id: customerId.toString() };
    return apiFetch<CustomerContact[]>('/api/ContactController.php', params)
        .then(contacts => contacts.map(c => ({
            ...c,
            is_primary: Boolean(c.is_primary)
        })));
};

export const fetchCustomerAddresses = (customerId: number): Promise<CustomerAddress[]> => {
    const params: ApiParams = { customer_id: customerId.toString() };
    return apiFetch<CustomerAddress[]>('/api/AddressController.php', params)
        .then(addresses => addresses.map(a => ({
            ...a,
            is_primary: Boolean(a.is_primary)
        })));
};

export const fetchCustomerActivity = (customerId: number): Promise<CustomerActivity[]> => {
    const params: ApiParams = { customer_id: customerId.toString() };
    return apiFetch<CustomerActivity[]>('/api/ActivityController.php', params);
};