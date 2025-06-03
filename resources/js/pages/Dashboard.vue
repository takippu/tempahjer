<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { LoaderCircle, CheckCircle, XCircle } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

// Get the current subdomain from the URL
const getCurrentSubdomain = () => {
    const hostname = window.location.hostname;
    const parts = hostname.split('.');
    if (parts.length > 2) {
        return parts[0];
    }
    return '';
};

// Initialize with current subdomain
const currentSubdomain = ref(getCurrentSubdomain());

// Fetch current domain from backend
const fetchCurrentDomain = async () => {
    try {
        const response = await axios.get('/api/subdomain/current');
        if (response.data && response.data.domain) {
            console.log('Domain from API:', response.data.domain);
            // Extract subdomain from domain
            const domain = response.data.domain;
            
            // For 'tempahjer.test', we want to extract 'test'
            if (domain === 'tempahjer.test') {
                currentSubdomain.value = 'test';
            } else {
                // For other domains like 'test.tempahjer.test', extract the first part
                const domainParts = domain.split('.');
                if (domainParts.length > 2) {
                    currentSubdomain.value = domainParts[0];
                } else if (domainParts.length === 2) {
                    // For other two-part domains, set empty or default value
                    currentSubdomain.value = '';
                }
            }
            
            console.log('Current subdomain set to:', currentSubdomain.value);
        }
    } catch (error) {
        console.error('Error fetching current domain:', error);
    }
};

onMounted(() => {
    // Ensure subdomain is set after component is mounted
    if (!currentSubdomain.value) {
        currentSubdomain.value = getCurrentSubdomain();
    }
    
    // Fetch current domain from backend
    fetchCurrentDomain();
});

const newSubdomain = ref('');
const isChecking = ref(false);
const isUpdating = ref(false);
const isAvailable = ref(false);
const message = ref('');
const showMessage = ref(false);
const updateMessage = ref('');
const showUpdateMessage = ref(false);
const updateSuccess = ref(false);

// Check if subdomain is available
const checkSubdomain = async () => {
    if (!newSubdomain.value) return;
    
    isChecking.value = true;
    showMessage.value = false;
    
    try {
        // Use direct URL instead of route helper
        const response = await axios.post('/api/subdomain/check', {
            subdomain: newSubdomain.value
        });
        
        isAvailable.value = response.data.available;
        message.value = response.data.message;
        showMessage.value = true;
    } catch (error) {
        console.error('Error checking subdomain:', error);
        message.value = 'An error occurred while checking subdomain availability.';
        showMessage.value = true;
        isAvailable.value = false;
    } finally {
        isChecking.value = false;
    }
};

// Update subdomain
const updateSubdomain = async () => {
    if (!newSubdomain.value || !isAvailable.value) return;
    
    isUpdating.value = true;
    showUpdateMessage.value = false;
    
    try {
        // Use direct URL instead of route helper
        const response = await axios.post('/api/subdomain/update', {
            current_subdomain: currentSubdomain.value,
            new_subdomain: newSubdomain.value
        });
        
        updateMessage.value = response.data.message;
        updateSuccess.value = true;
        showUpdateMessage.value = true;
        
        // Redirect to new subdomain after a short delay
        setTimeout(() => {
            const newDomain = `${newSubdomain.value}.tempahjer.test`;
            window.location.href = `http://${newDomain}/dashboard`;
        }, 3000);
    } catch (error) {
        console.error('Error updating subdomain:', error);
        updateMessage.value = error.response?.data?.message || 'An error occurred while updating the subdomain.';
        updateSuccess.value = false;
        showUpdateMessage.value = true;
    } finally {
        isUpdating.value = false;
    }
};

// Debounce function for subdomain checking
let debounceTimer;
const debouncedCheck = () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        checkSubdomain();
    }, 500);
};

// Watch for changes in the subdomain input
const handleSubdomainInput = () => {
    showMessage.value = false;
    if (newSubdomain.value) {
        debouncedCheck();
    }
};
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                </div>
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                </div>
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                </div>
            </div>
            
            <!-- Subdomain Management Section -->
            <div class="relative flex-1 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6 md:min-h-min">
                <h2 class="text-2xl font-semibold mb-4">Manage Your Subdomain</h2>
                <div class="max-w-md">
                    <div class="mb-6">
                        <Label for="current-subdomain" class="mb-2 block">Current Subdomain</Label>
                        <div class="flex items-center">
                            <Input 
                                id="current-subdomain" 
                                type="text" 
                                v-model="currentSubdomain" 
                                disabled 
                                class="rounded-r-none bg-muted" 
                            />
                            <span class="inline-flex items-center px-3 py-2 border border-l-0 border-input bg-muted text-muted-foreground text-sm rounded-r-md">.tempahjer.test</span>
                        </div>
                    </div>
                    
                    <div class="mb-6">
                        <Label for="new-subdomain" class="mb-2 block">New Subdomain</Label>
                        <div class="flex items-center">
                            <Input 
                                id="new-subdomain" 
                                type="text" 
                                v-model="newSubdomain" 
                                @input="handleSubdomainInput" 
                                placeholder="Enter new subdomain" 
                                class="rounded-r-none" 
                            />
                            <span class="inline-flex items-center px-3 py-2 border border-l-0 border-input bg-muted text-muted-foreground text-sm rounded-r-md">.tempahjer.test</span>
                        </div>
                        
                        <!-- Availability message -->
                        <div v-if="showMessage" class="mt-2 flex items-center gap-1" :class="isAvailable ? 'text-green-600' : 'text-red-600'">
                            <CheckCircle v-if="isAvailable" class="h-4 w-4" />
                            <XCircle v-else class="h-4 w-4" />
                            <span class="text-sm">{{ message }}</span>
                        </div>
                        
                        <!-- Loading indicator -->
                        <div v-if="isChecking" class="mt-2 flex items-center gap-1 text-muted-foreground">
                            <LoaderCircle class="h-4 w-4 animate-spin" />
                            <span class="text-sm">Checking availability...</span>
                        </div>
                    </div>
                    
                    <Button 
                        @click="updateSubdomain" 
                        :disabled="!isAvailable || isUpdating || !newSubdomain || newSubdomain === currentSubdomain"
                    >
                        <LoaderCircle v-if="isUpdating" class="h-4 w-4 animate-spin mr-2" />
                        Update Subdomain
                    </Button>
                    
                    <!-- Update result message -->
                    <div v-if="showUpdateMessage" class="mt-4 p-3 rounded-md" :class="updateSuccess ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                        <div class="flex items-center gap-2">
                            <CheckCircle v-if="updateSuccess" class="h-5 w-5" />
                            <XCircle v-else class="h-5 w-5" />
                            <span>{{ updateMessage }}</span>
                        </div>
                        <div v-if="updateSuccess" class="mt-2 text-sm">
                            Redirecting to your new subdomain in a moment...
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
