import { 
  Customer, 
  CustomerContact, 
  CustomerAddress, 
  CustomerActivity 
} from './models.js';

const API_BASE_URL = '/api';

export async function fetchCustomers(searchTerm: string = ''): Promise<Customer[]> {
  const url = searchTerm 
    ? `${API_BASE_URL}/customers.php?search=${encodeURIComponent(searchTerm)}`
    : `${API_BASE_URL}/customers.php`;
  
  const response = await fetch(url);
  if (!response.ok) throw new Error('Failed to fetch customers');
  return response.json();
}

export async function fetchCustomerDetails(customerId: number): Promise<Customer> {
  const response = await fetch(`${API_BASE_URL}/customers.php?id=${customerId}`);
  if (!response.ok) throw new Error('Failed to fetch customer details');
  return response.json();
}

export async function fetchCustomerContacts(customerId: number): Promise<CustomerContact[]> {
  const response = await fetch(`${API_BASE_URL}/contacts.php?customer_id=${customerId}`);
  if (!response.ok) throw new Error('Failed to fetch customer contacts');
  return response.json();
}

export async function fetchCustomerAddresses(customerId: number): Promise<CustomerAddress[]> {
  const response = await fetch(`${API_BASE_URL}/addresses.php?customer_id=${customerId}`);
  if (!response.ok) throw new Error('Failed to fetch customer addresses');
  return response.json();
}

export async function fetchCustomerActivity(customerId: number): Promise<CustomerActivity[]> {
  const response = await fetch(`${API_BASE_URL}/activity.php?customer_id=${customerId}`);
  if (!response.ok) throw new Error('Failed to fetch customer activity');
  return response.json();
}