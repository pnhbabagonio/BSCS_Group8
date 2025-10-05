<script setup lang="ts">
import { ref, onMounted } from "vue"
import { router } from "@inertiajs/vue3"
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'
import Requirements from "@/components/PaymentManagement/Requirements.vue"
import Records from "@/components/PaymentManagement/Records.vue"
import Profiles from "@/components/PaymentManagement/Profiles.vue"

// --- Types ---
interface PaymentRecord {
    id: number
    user_id: number | null
    requirement_id: number
    amount_paid: number
    paid_at: string | null
    status: "paid" | "unpaid" | "pending"
    payment_method: string | null
    notes: string | null
    first_name: string | null
    middle_name: string | null
    last_name: string | null
    student_id: string | null
    user: {
        id: number
        first_name: string
        middle_name: string | null
        last_name: string
        student_id: string | null
        full_name?: string
    } | null
    requirement: {
        id: number
        title: string
        amount: number
    }
    created_at: string
    updated_at: string
}

interface User {
    id: number
    first_name: string
    middle_name: string | null
    last_name: string
    student_id: string | null
    full_name?: string
}

interface Requirement {
    id: number
    title: string
    description: string
    amount: number
    deadline: string
    total_users: number
    paid: number
    unpaid: number
    created_at: string
    updated_at: string
}

// --- Reactive State with Proper Typing ---
const activeTab = ref("requirements")
const payments = ref<PaymentRecord[]>([])
const requirements = ref<Requirement[]>([])
const users = ref<User[]>([])
const isLoading = ref(false)

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Payment', href: '/payment' },
]

const tabs = [
    { key: "requirements", label: "Requirements" },
    { key: "records", label: "Payment Records" },
    { key: "profiles", label: "User Profiles" },
]

// Load requirements data from the requirements endpoint
function loadRequirements() {
    return new Promise((resolve, reject) => {
        router.get('/requirements', {}, {
            preserveState: true,
            preserveScroll: true,
            onSuccess: (page) => {
                console.log("Requirements data loaded:", page.props)
                
                // Check if requirements data is available
                if (page.props.requirements && Array.isArray(page.props.requirements)) {
                    requirements.value = page.props.requirements as Requirement[]
                    console.log(`Loaded ${requirements.value.length} requirements with full data`)
                } else {
                    console.warn("No requirements found in props or invalid format")
                    requirements.value = []
                }
                resolve(true)
            },
            onError: (errors) => {
                console.error('Error loading requirements:', errors)
                requirements.value = []
                reject(errors)
            }
        })
    })
}

// Load payment records and users data from the records endpoint
function loadPaymentRecords() {
    return new Promise((resolve, reject) => {
        router.get('/records', {
            search: '',
            status: 'All'
        }, {
            preserveState: true,
            preserveScroll: true,
            onSuccess: (page) => {
                console.log("Payment data loaded:", page.props)
                
                if (page.props.payments && Array.isArray(page.props.payments)) {
                    payments.value = page.props.payments as PaymentRecord[]
                    console.log(`Loaded ${payments.value.length} payments`)
                } else {
                    console.warn("No payments found in props or invalid format")
                    payments.value = []
                }
                
                if (page.props.users && Array.isArray(page.props.users)) {
                    users.value = page.props.users as User[]
                    console.log(`Loaded ${users.value.length} users`)
                } else {
                    console.warn("No users found in props or invalid format")
                    users.value = []
                }
                resolve(true)
            },
            onError: (errors) => {
                console.error('Error loading payment data:', errors)
                payments.value = []
                users.value = []
                reject(errors)
            }
        })
    })
}

// Load all data
async function loadPaymentData() {
    isLoading.value = true
    console.log("Loading all payment data...")
    
    try {
        // Load requirements first (they're needed for the requirements tab)
        await loadRequirements()
        
        // Then load payments and users (they're needed for the records tab)
        await loadPaymentRecords()
        
        console.log("All data loaded successfully")
    } catch (error) {
        console.error('Error loading data:', error)
    } finally {
        isLoading.value = false
    }
}

// Handle refresh from child component
function handleRefreshData() {
    console.log("Refresh data requested from child component")
    loadPaymentData()
}

// Load data when component mounts
onMounted(() => {
    console.log("PaymentManagement component mounted")
    loadPaymentData()
})
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 text-gray-200">
            <!-- Tabs Navigation -->
            <div class="flex space-x-6 border-b border-gray-800 mb-6">
                <button
                    v-for="tab in tabs"
                    :key="tab.key"
                    @click="activeTab = tab.key"
                    class="pb-2"
                    :class="[
                    activeTab === tab.key
                        ? 'border-b-2 border-blue-500 text-blue-400'
                        : 'text-gray-400 hover:text-gray-200'
                    ]"
                >
                    {{ tab.label }}
                </button>
            </div>

            <!-- Loading State -->
            <div v-if="isLoading" class="text-center py-8">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500 mx-auto"></div>
                <p class="text-muted-foreground mt-2">
                    {{ activeTab === 'requirements' ? 'Loading requirements...' : 
                       activeTab === 'records' ? 'Loading payment records...' : 
                       'Loading user profiles...' }}
                </p>
            </div>

            <!-- Tab Content -->
            <div v-else>
                <Requirements 
                    v-if="activeTab === 'requirements'" 
                    :requirements="requirements"
                    @refresh-data="handleRefreshData"
                />
                <Records 
                    v-if="activeTab === 'records'" 
                    :payments="payments"
                    :requirements="requirements"
                    :users="users"
                    @refresh-data="handleRefreshData"
                />
                <Profiles 
                    v-if="activeTab === 'profiles'" 
                    :users="users"
                    @refresh-data="handleRefreshData"
                />
            </div>
        </div>
    </AppLayout>
</template>