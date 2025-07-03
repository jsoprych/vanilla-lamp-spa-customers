// src/ts/apiUtils.ts
type ApiParams = Record<string, string>;

export async function apiFetch<T>(
    endpoint: string,
    params: ApiParams = {}
): Promise<T> {
    const url = new URL(endpoint, window.location.origin);
    Object.entries(params).forEach(([key, value]) => {
        url.searchParams.append(key, value);
    });

    const response = await fetch(url.toString());
    if (!response.ok) {
        throw new Error(`API request failed: ${response.statusText}`);
    }
    return response.json() as Promise<T>;
}