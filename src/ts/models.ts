// models.ts
export interface Customer {
    customer_id: number;
    customer_code: string;
    first_name: string;
    last_name: string;
    company_name: string;
    customer_type: 'individual' | 'business';
    tax_id: string;
    status: 'active' | 'inactive' | 'prospect' | 'lead' | 'banned';
    notes: string;
    created_at: string;
    updated_at: string;
}

export interface CustomerContact {
    contact_id: number;
    customer_id: number;
    contact_type: 'email' | 'phone' | 'mobile' | 'fax' | 'other';
    contact_value: string;
    is_primary: boolean;
    notes: string;
}

export interface CustomerAddress {
    address_id: number;
    customer_id: number;
    address_type: 'billing' | 'shipping' | 'home' | 'work' | 'other';
    line1: string;
    line2: string;
    city: string;
    state_province: string;
    postal_code: string;
    country: string;
    is_primary: boolean;
    notes: string;
}

export interface CustomerActivity {
    activity_id: number;
    customer_id: number;
    activity_type: string;
    activity_details: string;
    ip_address: string;
    user_agent: string;
    created_at: string;
}