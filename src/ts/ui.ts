// ui.ts
import { Customer, CustomerContact, CustomerAddress, CustomerActivity } from './models';

export function renderCustomerList(customers: Customer[], selectedCustomerId: number | null = null): void {
    const customerListElement = document.getElementById('customerList');
    if (!customerListElement) return;

    customerListElement.innerHTML = customers.map(customer => `
        <div class="customer-item ${customer.customer_id === selectedCustomerId ? 'active' : ''}" 
             data-customer-id="${customer.customer_id}">
            <h3>${customer.company_name || `${customer.first_name} ${customer.last_name}`}</h3>
            <p>${customer.customer_code} • ${customer.customer_type}</p>
        </div>
    `).join('');
}

export function renderCustomerDetail(customer: Customer): void {
    const customerDetailElement = document.getElementById('customerDetail');
    if (!customerDetailElement) return;

    customerDetailElement.innerHTML = `
        <div class="detail-header">
            <h2>${customer.company_name || `${customer.first_name} ${customer.last_name}`}</h2>
            <p class="customer-meta">${customer.customer_code} • ${customer.status}</p>
        </div>
    `;

    renderBasicInfoTab(customer);
}

function renderBasicInfoTab(customer: Customer): void {
    const infoTabElement = document.getElementById('infoTab');
    if (!infoTabElement) return;

    infoTabElement.innerHTML = `
        <div class="info-grid">
            <div class="info-item">
                <label>Customer Type</label>
                <span>${customer.customer_type}</span>
            </div>
            <div class="info-item">
                <label>Status</label>
                <span>${customer.status}</span>
            </div>
            <div class="info-item">
                <label>Tax ID</label>
                <span>${customer.tax_id || 'N/A'}</span>
            </div>
            <div class="info-item">
                <label>Created At</label>
                <span>${new Date(customer.created_at).toLocaleString()}</span>
            </div>
            <div class="info-item">
                <label>Updated At</label>
                <span>${new Date(customer.updated_at).toLocaleString()}</span>
            </div>
        </div>
        <div class="info-item">
            <label>Notes</label>
            <span>${customer.notes || 'No notes available'}</span>
        </div>
    `;
}

export function renderCustomerContacts(contacts: CustomerContact[]): void {
    const contactListElement = document.getElementById('contactList');
    if (!contactListElement) return;

    if (contacts.length === 0) {
        contactListElement.innerHTML = '<p>No contacts found for this customer.</p>';
        return;
    }

    contactListElement.innerHTML = contacts.map(contact => `
        <div class="contact-card">
            <h4>${contact.contact_type} ${contact.is_primary ? '<span class="primary-badge">Primary</span>' : ''}</h4>
            <p><strong>Value:</strong> ${contact.contact_value}</p>
            ${contact.notes ? `<p><strong>Notes:</strong> ${contact.notes}</p>` : ''}
        </div>
    `).join('');
}

export function renderCustomerAddresses(addresses: CustomerAddress[]): void {
    const addressListElement = document.getElementById('addressList');
    if (!addressListElement) return;

    if (addresses.length === 0) {
        addressListElement.innerHTML = '<p>No addresses found for this customer.</p>';
        return;
    }

    addressListElement.innerHTML = addresses.map(address => `
        <div class="address-card">
            <h4>${address.address_type} address ${address.is_primary ? '<span class="primary-badge">Primary</span>' : ''}</h4>
            <p>${address.line1}</p>
            ${address.line2 ? `<p>${address.line2}</p>` : ''}
            <p>${address.city}, ${address.state_province} ${address.postal_code}</p>
            <p>${address.country}</p>
            ${address.notes ? `<p><strong>Notes:</strong> ${address.notes}</p>` : ''}
        </div>
    `).join('');
}

export function renderCustomerActivity(activities: CustomerActivity[]): void {
    const activityListElement = document.getElementById('activityList');
    if (!activityListElement) return;

    if (activities.length === 0) {
        activityListElement.innerHTML = '<p>No activity found for this customer.</p>';
        return;
    }

    activityListElement.innerHTML = activities.map(activity => `
        <div class="activity-card">
            <h4>${activity.activity_type}</h4>
            <p><strong>Date:</strong> ${new Date(activity.created_at).toLocaleString()}</p>
            ${activity.activity_details ? `<p><strong>Details:</strong> ${activity.activity_details}</p>` : ''}
            ${activity.ip_address ? `<p><strong>IP:</strong> ${activity.ip_address}</p>` : ''}
        </div>
    `).join('');
}

export function setupTabNavigation(): void {
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            const tabId = button.getAttribute('data-tab');
            
            // Remove active class from all buttons and contents
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));
            
            // Add active class to clicked button and corresponding content
            button.classList.add('active');
            const tabContent = document.getElementById(`${tabId}Tab`);
            if (tabContent) {
                tabContent.classList.add('active');
            }
        });
    });
}

export function setupCustomerListClickHandler(onCustomerSelected: (customerId: number) => void): void {
    document.getElementById('customerList')?.addEventListener('click', (event) => {
        const customerItem = (event.target as HTMLElement).closest('.customer-item');
        if (customerItem) {
            const customerId = parseInt(customerItem.getAttribute('data-customer-id') || '0');
            if (customerId) {
                onCustomerSelected(customerId);
            }
        }
    });
}

export function setupSearchHandler(onSearch: (searchTerm: string) => void): void {
    const searchBtn = document.getElementById('searchBtn');
    const searchInput = document.getElementById('searchInput') as HTMLInputElement;

    const performSearch = () => {
        onSearch(searchInput.value.trim());
    };

    searchBtn?.addEventListener('click', performSearch);
    searchInput?.addEventListener('keyup', (event) => {
        if (event.key === 'Enter') {
            performSearch();
        }
    });
}