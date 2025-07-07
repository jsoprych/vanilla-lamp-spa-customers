import { Customer, CustomerContact, CustomerAddress, CustomerActivity } from './models';

type ApiParams = Record<string, string>;

export const fetchCustomers = (searchTerm: string = ''): Promise<Customer[]> => {
    const params: ApiParams = searchTerm ? { search: searchTerm } : {};
    console.log('Fetching customers, params:', params);
    return apiFetch<Customer[]>('/api/CustomerController.php', params).then(data => {
        console.log('Customers data received:', data);
        return data;
    }).catch(error => {
        console.error('Fetch customers error:', {
            error: error.message,
            stack: error.stack,
            params: params,
            url: '/api/CustomerController.php'
        });
        throw error;
    });
};

export const fetchCustomerDetails = (customerId: number): Promise<Customer> => {
    const params: ApiParams = { customer_id: customerId.toString() };
    console.log('Fetching customer details, customerId:', customerId, 'params:', params);
    return apiFetch<Customer>('/api/CustomerController.php', params).then(data => {
        console.log('Customer details received:', data);
        return data;
    }).catch(error => {
        console.error('Fetch customer details error:', {
            error: error.message,
            stack: error.stack,
            params: params,
            url: '/api/CustomerController.php',
            rawResponse: (error as any).rawResponse || 'Not available'
        });
        throw error;
    });
};

export const fetchCustomerContacts = (customerId: number): Promise<CustomerContact[]> => {
    const params: ApiParams = { customer_id: customerId.toString() };
    console.log('Fetching contacts, customerId:', customerId, 'params:', params);
    return apiFetch<CustomerContact[]>('/api/ContactController.php', params)
        .then(contacts => {
            console.log('Contacts received:', contacts);
            return contacts.map(c => ({ ...c, is_primary: Boolean(c.is_primary) }));
        }).catch(error => {
            console.error('Fetch contacts error:', {
                error: error.message,
                stack: error.stack,
                params: params,
                url: '/api/ContactController.php',
                rawResponse: (error as any).rawResponse || 'Not available'
            });
            throw error;
        });
};

export const fetchCustomerAddresses = (customerId: number): Promise<CustomerAddress[]> => {
    const params: ApiParams = { customer_id: customerId.toString() };
    console.log('Fetching addresses, customerId:', customerId, 'params:', params);
    return apiFetch<CustomerAddress[]>('/api/AddressController.php', params)
        .then(addresses => {
            console.log('Addresses received:', addresses);
            return addresses.map(a => ({ ...a, is_primary: Boolean(a.is_primary) }));
        }).catch(error => {
            console.error('Fetch addresses error:', {
                error: error.message,
                stack: error.stack,
                params: params,
                url: '/api/AddressController.php',
                rawResponse: (error as any).rawResponse || 'Not available'
            });
            throw error;
        });
};

export const fetchCustomerActivity = (customerId: number): Promise<CustomerActivity[]> => {
    const params: ApiParams = { customer_id: customerId.toString() };
    console.log('Fetching activity, customerId:', customerId, 'params:', params);
    return apiFetch<CustomerActivity[]>('/api/ActivityController.php', params).catch(error => {
        console.error('Fetch activity error:', {
            error: error.message,
            stack: error.stack,
            params: params,
            url: '/api/ActivityController.php',
            rawResponse: (error as any).rawResponse || 'Not available'
        });
        throw error;
    });
};

export const apiFetch = async <T>(url: string, params: Record<string, string> = {}): Promise<T> => {
    const queryString = new URLSearchParams(params).toString();
    console.log(`Fetching from ${url} with params: ${queryString}`);
    const response = await fetch(`${url}${queryString ? '?' + queryString : ''}`, {
        method: 'GET',
        headers: { 'Content-Type': 'application/json' },
    });
    if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
    }
    const text = await response.text();
    console.log(`Raw response from ${url}:`, text);
    if (!text) {
        const error = new Error('Empty response received');
        (error as any).rawResponse = text;
        throw error;
    }
    try {
        const json = JSON.parse(text);
        return json.data; // Extract data from the response
    } catch (parseError) {
        const error = new Error('Invalid JSON response');
        (error as any).rawResponse = text;
        throw error;
    }
};