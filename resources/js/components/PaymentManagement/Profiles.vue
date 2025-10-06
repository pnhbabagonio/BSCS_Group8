<script setup lang="ts">
import { ref, computed, onMounted, watch } from "vue"
import { router } from "@inertiajs/vue3"
import {
    Search,
    Plus,
    Filter,
    ChevronDown,
    X,
    Pencil,
    Trash2,
    Eye,
    Download,
    QrCode,
} from "lucide-vue-next"
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';

// --- Types ---
interface PaymentRequirement {
    id: number
    title: string
    amount: number
    amount_paid?: number
    paid_at?: string
    payment_method?: string
    overdue?: boolean
    deadline?: string
}

interface PaymentHistory {
    id: number
    requirement_title: string
    amount_paid: number
    status: string
    paid_at: string | null
    payment_method: string | null
    notes: string | null
    created_at: string
}

interface UserProfile {
    id: string | number
    type: 'user' | 'manual'
    first_name: string
    middle_name: string | null
    last_name: string
    full_name: string
    student_id: string | null
    program: string
    year: string
    section: string
    total_balance: number
    total_paid: number
    total_unpaid: number
    paid_requirements: PaymentRequirement[]
    unpaid_requirements: PaymentRequirement[]
    payment_history: PaymentHistory[]
    paid_requirements_count: number
    unpaid_requirements_count: number
}

// --- Props & Emits ---
const props = defineProps<{
    users: UserProfile[]
}>()

const emit = defineEmits<{
    (e: 'refresh-data'): void
}>()

// --- State ---
const search = ref("")
const isFilterOpen = ref(false)
const courseFilter = ref("All")
const filterOptions = ref<string[]>([])

// Modal states
const showModal = ref(false)
const showDetailsModal = ref(false)
const selectedUser = ref<UserProfile | null>(null)

// Requirement-specific states
const requirementSearch = ref("")
const requirementFilter = ref("All")
const isRequirementFilterOpen = ref(false)
const isLoading = ref(false)

// Use props data directly
const users = ref<UserProfile[]>(props.users || [])

// Watch for prop changes
watch(() => props.users, (newUsers) => {
    users.value = newUsers || []
})

// Initialize filter options from props
onMounted(() => {
    // Extract unique programs from users for filter options
    const programs = [...new Set(users.value.map(user => user.program))].filter(Boolean)
    filterOptions.value = ['All', ...programs]
})

// --- Filtered users ---
const filteredUsers = computed(() => {
    return users.value.filter((user) => {
        const matchesSearch = user.full_name.toLowerCase().includes(search.value.toLowerCase()) ||
                            user.student_id?.toLowerCase().includes(search.value.toLowerCase()) ||
                            user.program.toLowerCase().includes(search.value.toLowerCase())

        if (courseFilter.value === "All") return matchesSearch
        return user.program === courseFilter.value && matchesSearch
    })
})

// --- Methods ---
// function openDetails(user: UserProfile) {
//     selectedUser.value = user
//     // Reset requirement filters/search when opening details
//     requirementSearch.value = ""
//     requirementFilter.value = "All"
//     isRequirementFilterOpen.value = false
//     showDetailsModal.value = true
    
//     // If you want to load detailed data, you can call the API here:
//     // loadUserDetails(user.id)
// }


// This is a temporary method for testing
function openDetails(user: UserProfile) {
    console.log("Opening details for user:", user)
    console.log("Paid requirements:", user.paid_requirements)
    console.log("Unpaid requirements:", user.unpaid_requirements)
    console.log("Payment history:", user.payment_history)
    
    selectedUser.value = user
    // Reset requirement filters/search when opening details
    requirementSearch.value = ""
    requirementFilter.value = "All"
    isRequirementFilterOpen.value = false
    showDetailsModal.value = true
}


function exportReceipt(user: UserProfile | null) {
    if (!user) return
    alert(`Exporting receipt for ${user.full_name}`)
    // Implement actual export logic here
}

function scanQR() {
    alert('QR scanning functionality will be implemented in the future')
}

function clearFilters() {
    courseFilter.value = 'All'
    search.value = ''
}

// Combined requirements for detail view
type CombinedReq = PaymentRequirement & {
    status: 'paid' | 'unpaid'
    date: string
}

const combinedRequirements = computed<CombinedReq[]>(() => {
    if (!selectedUser.value) return []

    const paid: CombinedReq[] = selectedUser.value.paid_requirements.map(req => ({
        ...req,
        status: 'paid' as const,
        date: req.paid_at || new Date().toISOString(),
    }))

    const unpaid: CombinedReq[] = selectedUser.value.unpaid_requirements.map(req => ({
        ...req,
        status: 'unpaid' as const,
        date: req.deadline || new Date().toISOString(),
    }))

    return [...paid, ...unpaid]
})

// Filtered requirements for detail view
const filteredRequirements = computed<CombinedReq[]>(() => {
    const all = combinedRequirements.value

    // Apply search
    const searched = all.filter((r) =>
        r.title.toLowerCase().includes(requirementSearch.value.toLowerCase())
    )

    // Apply filter
    if (requirementFilter.value === "Paid") {
        return searched.filter((r) => r.status === 'paid')
    } else if (requirementFilter.value === "Unpaid") {
        return searched.filter((r) => r.status === 'unpaid')
    } else if (requirementFilter.value === "Recent") {
        return [...searched]
            .sort((a, b) => new Date(b.date).getTime() - new Date(a.date).getTime())
            .slice(0, 5)
    } else if (requirementFilter.value === "Old") {
        return [...searched]
            .sort((a, b) => new Date(a.date).getTime() - new Date(b.date).getTime())
            .slice(0, 5)
    }

    // All
    return searched
})

// Load detailed user data (optional - for future enhancement)
async function loadUserDetails(userId: string | number) {
    isLoading.value = true
    try {
        const response = await fetch(`/user-profiles/${userId}`)
        const userData = await response.json()
        selectedUser.value = userData
    } catch (error) {
        console.error('Error loading user details:', error)
    } finally {
        isLoading.value = false
    }
}

// Refresh data
function refreshData() {
    emit('refresh-data')
}
</script>

<template>
    <div class="p-6 text-foreground">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-2xl font-bold">User Payment Profiles</h2>
                <p class="text-muted-foreground">View student payment records and requirements</p>
            </div>
            <div class="flex items-center gap-3">
                <Button
                    @click="scanQR"
                    variant="outline"
                    class="gap-2"
                    disabled
                >
                    <QrCode class="w-4 h-4" /> Scan QR
                </Button>
                <Button
                    @click="refreshData"
                    variant="outline"
                    class="gap-2"
                >
                    Refresh Data
                </Button>
            </div>
        </div>

        <!-- Search + Filter -->
        <div class="flex items-center gap-3 mb-6">
            <!-- Search -->
            <div class="flex items-center flex-1 bg-muted border border-border rounded-lg px-3 py-2">
                <Search class="w-4 h-4 text-muted-foreground" />
                <input
                    v-model="search"
                    type="text"
                    placeholder="Search users..."
                    class="ml-2 flex-1 bg-transparent text-sm focus:outline-none placeholder:text-muted-foreground"
                />
            </div>

            <!-- Filter (course) -->
            <div class="relative">
                <Button
                    @click="isFilterOpen = !isFilterOpen"
                    variant="outline"
                    class="gap-2"
                >
                    <Filter class="w-4 h-4" />
                    <span>{{ courseFilter }}</span>
                    <ChevronDown
                        class="w-4 h-4 transition-transform"
                        :class="{ 'rotate-180': isFilterOpen }"
                    />
                </Button>

                <!-- Dropdown -->
                <div
                    v-if="isFilterOpen"
                    class="absolute right-0 mt-2 w-40 bg-card border border-border rounded-lg shadow-lg z-10 overflow-hidden"
                >
                    <button
                        v-for="option in filterOptions"
                        :key="option"
                        @click="courseFilter = option; isFilterOpen = false"
                        class="w-full text-left px-3 py-2 text-sm hover:bg-muted"
                        :class="{ 'bg-muted': courseFilter === option }"
                    >
                        {{ option }}
                    </button>
                </div>
            </div>

            <!-- Clear Filter -->
            <Button
                v-if="courseFilter !== 'All' || search"
                @click="clearFilters"
                variant="outline"
            >
                Clear
            </Button>
        </div>

        <!-- Loading State -->
        <div v-if="isLoading" class="text-center py-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500 mx-auto"></div>
            <p class="text-muted-foreground mt-2">Loading user profiles...</p>
        </div>

        <!-- User Cards -->
        <div v-else class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div
                v-for="user in filteredUsers"
                :key="user.id"
                class="bg-card rounded-xl border border-border p-5"
            >
                <!-- Profile Info -->
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center">
                        <span class="text-primary font-bold text-sm">
                            {{ user.first_name.charAt(0) }}{{ user.last_name.charAt(0) }}
                        </span>
                    </div>
                    <div>
                        <h3 class="font-semibold">
                            {{ user.full_name }}
                        </h3>
                        <p class="text-sm text-muted-foreground">{{ user.program }}</p>
                        <p class="text-xs text-muted-foreground">
                            {{ user.student_id }} • {{ user.year }}
                            <Badge v-if="user.type === 'manual'" variant="outline" class="ml-1 text-xs">
                                Manual
                            </Badge>
                        </p>
                    </div>
                </div>

                <!-- Payment Stats -->
                <div class="text-sm space-y-1 mb-4">
                    <p>
                        <span class="font-medium text-muted-foreground">Balance:</span>
                        <span class="ml-2 font-semibold">₱{{ user.total_balance.toLocaleString() }}</span>
                    </p>
                    <p>
                        <span class="font-medium text-muted-foreground">Paid:</span>
                        <span class="ml-2 text-green-600 font-semibold">₱{{ user.total_paid.toLocaleString() }}</span>
                    </p>
                    <p>
                        <span class="font-medium text-muted-foreground">Unpaid:</span>
                        <span class="ml-2 text-red-600 font-semibold">₱{{ user.total_unpaid.toLocaleString() }}</span>
                    </p>
                </div>

                <!-- Requirement Progress -->
                <div class="grid grid-cols-2 gap-2 mb-4">
                    <!-- Paid -->
                    <div
                        class="bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400 rounded-lg flex flex-col items-center justify-center py-3"
                    >
                        <span class="text-lg font-bold">✔ {{ user.paid_requirements_count }}</span>
                        <span class="text-xs">Paid Requirements</span>
                    </div>

                    <!-- Unpaid -->
                    <div
                        class="bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400 rounded-lg flex flex-col items-center justify-center py-3"
                    >
                        <span class="text-lg font-bold">✘ {{ user.unpaid_requirements_count }}</span>
                        <span class="text-xs">Unpaid Requirements</span>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-2">
                    <Button
                        @click="openDetails(user)"
                        class="flex-1 gap-2"
                    >
                        <Eye class="w-4 h-4" /> View Details
                    </Button>
                </div>
            </div>
        </div>

        <!-- Empty state -->
        <div v-if="filteredUsers.length === 0 && !isLoading" class="text-center py-12 text-muted-foreground">
            <Search class="h-12 w-12 mx-auto mb-4 opacity-50" />
            <p>No users with payment records found</p>
        </div>

        <!-- View Details Modal -->
        <div
            v-if="showDetailsModal && selectedUser"
            class="fixed inset-0 bg-background/80 backdrop-blur-sm flex items-center justify-center z-50 p-4"
        >
            <div
                class="bg-card border border-border p-8 rounded-xl shadow-lg w-[950px] max-h-[95vh] overflow-y-auto flex flex-col"
            >
                <!-- Header -->
                <div class="flex justify-between items-center border-b border-border pb-4 mb-6">
                    <div>
                        <h2 class="text-2xl font-bold">
                            {{ selectedUser.full_name }}
                        </h2>
                        <p class="text-muted-foreground">
                            {{ selectedUser.student_id }} • {{ selectedUser.program }} • {{ selectedUser.year }}
                            <Badge v-if="selectedUser.type === 'manual'" variant="outline" class="ml-2">
                                Manual Entry
                            </Badge>
                        </p>
                    </div>
                    <Button
                        @click="showDetailsModal = false"
                        variant="ghost"
                        size="icon"
                    >
                        <X class="w-6 h-6" />
                    </Button>
                </div>

                <!-- Two Column Layout -->
                <div class="grid grid-cols-2 gap-6">
                    <!-- Left Column: Student Info -->
                    <div class="space-y-3 bg-muted p-5 rounded-lg border border-border">
                        <p class="text-lg">
                            <span class="font-semibold text-muted-foreground">Program:</span>
                            <span class="ml-2 font-bold">{{ selectedUser.program }}</span>
                        </p>
                        <p class="text-lg">
                            <span class="font-semibold text-muted-foreground">Year:</span>
                            <span class="ml-2 font-bold">{{ selectedUser.year }}</span>
                        </p>
                        <p class="text-lg">
                            <span class="font-semibold text-muted-foreground">Student ID:</span>
                            <span class="ml-2 font-bold">{{ selectedUser.student_id || 'N/A' }}</span>
                        </p>
                        <p class="text-lg">
                            <span class="font-semibold text-muted-foreground">Total Balance:</span>
                            <span class="ml-2 font-bold text-blue-600">₱{{ selectedUser.total_balance.toLocaleString() }}</span>
                        </p>
                        <p class="text-lg">
                            <span class="font-semibold text-muted-foreground">Total Paid:</span>
                            <span class="ml-2 font-bold text-green-600">₱{{ selectedUser.total_paid.toLocaleString() }}</span>
                        </p>
                        <p class="text-lg">
                            <span class="font-semibold text-muted-foreground">Total Unpaid:</span>
                            <span class="ml-2 font-bold text-red-600">₱{{ selectedUser.total_unpaid.toLocaleString() }}</span>
                        </p>
                        <p class="text-lg">
                            <span class="font-semibold text-muted-foreground">Paid Requirements:</span>
                            <span class="ml-2 font-bold text-green-600">{{ selectedUser.paid_requirements_count }}</span>
                        </p>
                        <p class="text-lg">
                            <span class="font-semibold text-muted-foreground">Unpaid Requirements:</span>
                            <span class="ml-2 font-bold text-red-600">{{ selectedUser.unpaid_requirements_count }}</span>
                        </p>
                    </div>

                    <!-- Right Column: Requirements -->
                    <div class="space-y-4">
                        <!-- Search + Filter -->
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center flex-1 bg-muted border border-border rounded-lg px-3 py-2 mr-2">
                                <Search class="w-4 h-4 text-muted-foreground" />
                                <input
                                    v-model="requirementSearch"
                                    type="text"
                                    placeholder="Search requirements..."
                                    class="ml-2 flex-1 bg-transparent text-sm focus:outline-none placeholder:text-muted-foreground"
                                />
                            </div>
                            <div class="relative">
                                <Button
                                    @click="isRequirementFilterOpen = !isRequirementFilterOpen"
                                    variant="outline"
                                    class="gap-2"
                                >
                                    <Filter class="w-4 h-4" /> {{ requirementFilter }}
                                    <ChevronDown class="w-4 h-4" />
                                </Button>
                                <div
                                    v-if="isRequirementFilterOpen"
                                    class="absolute right-0 mt-2 bg-card border border-border rounded-lg shadow-lg w-40 z-10 overflow-hidden"
                                >
                                    <button
                                        v-for="opt in ['All','Recent','Old','Paid','Unpaid']"
                                        :key="opt"
                                        @click="requirementFilter = opt; isRequirementFilterOpen = false"
                                        class="block w-full text-left px-4 py-2 text-sm hover:bg-muted"
                                    >
                                        {{ opt }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Combined Requirements List -->
                        <div class="space-y-3 max-h-[400px] overflow-y-auto pr-2">
                            <div
                                v-for="(req, i) in filteredRequirements"
                                :key="i"
                                class="p-4 rounded-lg border border-border bg-muted flex justify-between items-center"
                            >
                                <div class="flex-1">
                                    <p class="font-medium">
                                        <span v-if="req.status === 'paid'" class="text-green-600">✔</span>
                                        <span v-else class="text-red-600">✘</span>
                                        {{ req.title }}
                                    </p>
                                    <div class="flex items-center gap-4 mt-1 text-sm text-muted-foreground">
                                        <span>Amount: ₱{{ req.amount.toLocaleString() }}</span>
                                        <span v-if="req.amount_paid">Paid: ₱{{ req.amount_paid.toLocaleString() }}</span>
                                        <span v-if="req.paid_at">Date: {{ new Date(req.paid_at).toLocaleDateString() }}</span>
                                        <span v-if="req.deadline">Due: {{ new Date(req.deadline).toLocaleDateString() }}</span>
                                    </div>
                                    <div class="flex items-center gap-2 mt-1">
                                        <Badge v-if="req.overdue" variant="destructive" class="text-xs">
                                            Overdue
                                        </Badge>
                                        <Badge v-if="req.payment_method" variant="outline" class="text-xs">
                                            {{ req.payment_method }}
                                        </Badge>
                                        <Badge v-if="req.status === 'paid'" variant="secondary" class="text-xs">
                                            Paid
                                        </Badge>
                                        <Badge v-else variant="outline" class="text-xs">
                                            Unpaid
                                        </Badge>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Empty state for requirements -->
                        <div v-if="filteredRequirements.length === 0" class="text-center py-8 text-muted-foreground">
                            <Search class="h-8 w-8 mx-auto mb-2 opacity-50" />
                            <p>No requirements found</p>
                        </div>
                    </div>
                </div>

                <!-- Payment History Section -->
                <div class="mt-6 border-t border-border pt-4">
                    <h3 class="text-lg font-semibold mb-4">Payment History</h3>
                    <div class="space-y-3 max-h-[300px] overflow-y-auto">
                        <div
                            v-for="payment in selectedUser.payment_history"
                            :key="payment.id"
                            class="p-3 rounded-lg border border-border bg-muted/50 flex justify-between items-center"
                        >
                            <div>
                                <p class="font-medium">{{ payment.requirement_title }}</p>
                                <p class="text-sm text-muted-foreground">
                                    {{ payment.paid_at ? new Date(payment.paid_at).toLocaleDateString() : 'No date' }} • 
                                    {{ payment.payment_method || 'No method' }}
                                </p>
                                <p class="text-xs text-muted-foreground" v-if="payment.notes">
                                    Notes: {{ payment.notes }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold">₱{{ payment.amount_paid.toLocaleString() }}</p>
                                <Badge 
                                    :variant="payment.status === 'paid' ? 'default' : 'outline'"
                                    class="text-xs"
                                >
                                    {{ payment.status }}
                                </Badge>
                            </div>
                        </div>
                    </div>
                    <div v-if="selectedUser.payment_history.length === 0" class="text-center py-4 text-muted-foreground">
                        <p>No payment history found</p>
                    </div>
                </div>

                <!-- Footer -->
                <div class="flex justify-end gap-2 mt-6 border-t border-border pt-4">
                    <Button
                        @click="exportReceipt(selectedUser)"
                        class="gap-2"
                    >
                        <Download class="w-4 h-4" /> Export Receipt
                    </Button>
                    <Button
                        @click="showDetailsModal = false"
                        variant="outline"
                    >
                        Close
                    </Button>
                </div>
            </div>
        </div>
    </div>
</template>