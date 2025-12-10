<script setup lang="ts">
import { ref, computed, watch, onMounted } from "vue"
import { router, usePage } from "@inertiajs/vue3"
import {
    Search,
    Plus,
    Filter,
    ChevronDown,
    X,
    Pencil,
    Trash2,
    QrCode,
    User,
    UserPlus,
    Download,
    Check,
} from "lucide-vue-next"
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';

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
    amount: number
}

// --- Props & Emits ---
const props = defineProps<{
    payments: PaymentRecord[]
    requirements: Requirement[]
    users: User[]
}>()

const emit = defineEmits<{
    (e: 'refresh-data'): void
}>()

// --- State ---
const showModal = ref(false)
const editingRecord = ref<PaymentRecord | null>(null)
const manualEntry = ref(false)
const isLoading = ref(false)
const isSelectionMode = ref(false)
const selectedRecords = ref<Set<number>>(new Set())

const search = ref("")
const isFilterOpen = ref(false)
const paymentFilter = ref("All")
const filterOptions = ["All", "paid", "unpaid", "pending"]

// Use props data directly - initialize with props
const payments = ref<PaymentRecord[]>(props.payments || [])
const requirements = ref<Requirement[]>(props.requirements || [])
const users = ref<User[]>(props.users || [])

// --- Modal Form Data ---
const form = ref({
    user_id: "",
    requirement_id: "",
    amount_paid: 0,
    paid_at: "",
    status: "pending" as "paid" | "unpaid" | "pending",
    payment_method: "",
    notes: "",
    first_name: "",
    middle_name: "",
    last_name: "",
    student_id: "",
})

// --- Computed Properties ---
const selectedRequirement = computed(() => {
    if (!form.value.requirement_id) return null
    return requirements.value.find(req => req.id === parseInt(form.value.requirement_id))
})

const isAllSelected = computed(() => {
    return filteredRecords.value.length > 0 && filteredRecords.value.every(record => selectedRecords.value.has(record.id))
})

const isAnySelected = computed(() => {
    return selectedRecords.value.size > 0
})

// Watch for prop changes and update local data
watch(() => props.payments, (newPayments) => {
    payments.value = newPayments || []
    console.log(`Updated payments from props: ${payments.value.length} records`)
})

watch(() => props.requirements, (newRequirements) => {
    requirements.value = newRequirements || []
    console.log(`Updated requirements from props: ${requirements.value.length} records`)
})

watch(() => props.users, (newUsers) => {
    users.value = newUsers || []
    console.log(`Updated users from props: ${users.value.length} records`)
})

// --- Methods ---
function toggleSelectionMode() {
    isSelectionMode.value = !isSelectionMode.value
    if (!isSelectionMode.value) {
        selectedRecords.value.clear()
    }
}

function toggleRecordSelection(recordId: number) {
    if (selectedRecords.value.has(recordId)) {
        selectedRecords.value.delete(recordId)
    } else {
        selectedRecords.value.add(recordId)
    }
}

function toggleSelectAll() {
    if (isAllSelected.value) {
        selectedRecords.value.clear()
    } else {
        filteredRecords.value.forEach(record => {
            selectedRecords.value.add(record.id)
        })
    }
}

function downloadSelectedRecords() {
    if (selectedRecords.value.size === 0) {
        alert('Please select at least one record to download')
        return
    }

    const selectedPayments = payments.value.filter(payment =>
        selectedRecords.value.has(payment.id)
    )

    // CSV headers
    const headers = ['ID', 'Student Name', 'Student ID', 'Requirement', 'Amount Paid', 'Date Paid', 'Payment Method', 'Status', 'Notes']

    // CSV rows
    const rows = selectedPayments.map(payment => [
        payment.id,
        `"${getDisplayName(payment)}"`,
        payment.user_id && payment.user ? payment.user.student_id || '' : payment.student_id || '',
        `"${payment.requirement.title}"`,
        payment.amount_paid,
        payment.paid_at ? formatDate(payment.paid_at) : '',
        payment.payment_method || '',
        payment.status,
        `"${payment.notes || ''}"`,
    ])

    // Combine headers and rows
    const csvContent = [
        headers.join(','),
        ...rows.map(row => row.join(','))
    ].join('\n')

    // Create blob and trigger download
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
    const link = document.createElement('a')
    const url = URL.createObjectURL(blob)

    link.setAttribute('href', url)
    link.setAttribute('download', `payment_records_${new Date().toISOString().split('T')[0]}.csv`)
    link.style.visibility = 'hidden'

    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)

    // Reset selection
    selectedRecords.value.clear()
    isSelectionMode.value = false
}

function loadRecords() {
    // Emit event to parent to refresh data
    console.log("Emitting refresh-data event to parent")
    emit('refresh-data')
}

function openAddModal() {
    editingRecord.value = null
    manualEntry.value = false
    form.value = {
        user_id: "",
        requirement_id: "",
        amount_paid: 0,
        paid_at: "",
        status: "pending",
        payment_method: "",
        notes: "",
        first_name: "",
        middle_name: "",
        last_name: "",
        student_id: "",
    }
    showModal.value = true
}

function openEditModal(record: PaymentRecord) {
    editingRecord.value = record
    manualEntry.value = !record.user_id

    form.value = {
        user_id: record.user_id ? record.user_id.toString() : "",
        requirement_id: record.requirement_id.toString(),
        amount_paid: record.amount_paid,
        paid_at: record.paid_at ? record.paid_at.split('T')[0] : "",
        status: record.status,
        payment_method: record.payment_method || "",
        notes: record.notes || "",
        first_name: record.first_name || "",
        middle_name: record.middle_name || "",
        last_name: record.last_name || "",
        student_id: record.student_id || "",
    }
    showModal.value = true
}

function saveRecord() {
    if (!form.value.requirement_id) {
        alert("Please select a requirement")
        return
    }

    if (!manualEntry.value && !form.value.user_id) {
        alert("Please select a user or switch to manual entry")
        return
    }

    if (manualEntry.value && (!form.value.first_name || !form.value.last_name)) {
        alert("Please enter first name and last name for manual entry")
        return
    }

    // Prepare the data properly
    const submitData: {
        requirement_id: number
        amount_paid: number
        status: "paid" | "unpaid" | "pending"
        payment_method: string | null
        notes: string | null
        paid_at: string | null
        first_name?: string
        middle_name?: string | null
        last_name?: string
        student_id?: string | null
        user_id?: number | null
    } = {
        requirement_id: parseInt(form.value.requirement_id),
        amount_paid: parseFloat(form.value.amount_paid.toString()),
        status: form.value.status,
        payment_method: form.value.payment_method || null,
        notes: form.value.notes || null,
        paid_at: form.value.paid_at || null,
    }

    // Handle user data based on entry type
    if (manualEntry.value) {
        // Manual entry - use name fields
        submitData.first_name = form.value.first_name
        submitData.middle_name = form.value.middle_name || null
        submitData.last_name = form.value.last_name
        submitData.student_id = form.value.student_id || null
        submitData.user_id = null
    } else {
        // Existing user - use user_id
        submitData.user_id = parseInt(form.value.user_id)
    }

    console.log("Submitting data:", submitData)

    if (editingRecord.value) {
        router.put(`/records/${editingRecord.value.id}`, submitData, {
            preserveScroll: true,
            onSuccess: () => {
                showModal.value = false
                loadRecords() // This will emit to parent to refresh data
            },
            onError: (errors) => {
                console.error('Error updating record:', errors)
                alert('Error updating record. Please check the console for details.')
            }
        })
    } else {
        router.post('/records', submitData, {
            preserveScroll: true,
            onSuccess: () => {
                showModal.value = false
                loadRecords() // This will emit to parent to refresh data
            },
            onError: (errors) => {
                console.error('Error creating record:', errors)
                // Show specific error messages if available
                if (errors && typeof errors === 'object') {
                    const errorMessages = Object.values(errors).join('\n')
                    alert(`Error creating record:\n${errorMessages}`)
                } else {
                    alert('Error creating record. Please check the console for details.')
                }
            }
        })
    }
}

function deleteRecord(id: number) {
    if (confirm('Are you sure you want to delete this payment record?')) {
        router.delete(`/records/${id}`, {
            preserveScroll: true,
            onSuccess: () => {
                loadRecords() // This will emit to parent to refresh data
            },
            onError: (errors) => {
                console.error('Error deleting record:', errors)
            }
        })
    }
}

function toggleManualEntry() {
    manualEntry.value = !manualEntry.value
    if (manualEntry.value) {
        form.value.user_id = ""
    } else {
        form.value.first_name = ""
        form.value.middle_name = ""
        form.value.last_name = ""
        form.value.student_id = ""
    }
}

function onRequirementChange() {
    if (selectedRequirement.value) {
        form.value.amount_paid = selectedRequirement.value.amount
    }
}

function clearFilters() {
    paymentFilter.value = 'All'
    search.value = ''
    // No need to call loadRecords() here since we're using client-side filtering
}

// Get display name for user dropdown
function getUserDisplayName(user: User) {
    if (user.full_name) {
        return user.full_name
    }
    return `${user.first_name} ${user.middle_name || ''} ${user.last_name}`.trim()
}

// --- Filtering ---
// Client-side filtering only since data is passed as props
const filteredRecords = computed(() => {
    let filtered = payments.value

    if (search.value) {
        const searchLower = search.value.toLowerCase()
        filtered = filtered.filter(record =>
            getDisplayName(record).toLowerCase().includes(searchLower) ||
            record.requirement.title.toLowerCase().includes(searchLower)
        )
    }

    if (paymentFilter.value !== 'All') {
        filtered = filtered.filter(record => record.status === paymentFilter.value)
    }

    return filtered
})

// Format date for display
function formatDate(dateString: string | null) {
    if (!dateString) return "—"
    return new Date(dateString).toLocaleDateString()
}

// Get user full name for display
function getDisplayName(record: PaymentRecord) {
    if (record.user_id && record.user) {
        return `${record.user.first_name} ${record.user.middle_name || ''} ${record.user.last_name}`.trim()
    }
    return `${record.first_name} ${record.middle_name || ''} ${record.last_name}`.trim()
}

// Get user details for display
function getUserDetails(record: PaymentRecord) {
    if (record.user_id && record.user) {
        return record.user.student_id ? `(${record.user.student_id})` : '(Registered User)'
    }
    return record.student_id ? `(${record.student_id})` : '(Manual Entry)'
}

// Initialize with props when component mounts
onMounted(() => {
    console.log("Records component mounted")
    console.log(`Initial props - Payments: ${props.payments?.length || 0}, Requirements: ${props.requirements?.length || 0}, Users: ${props.users?.length || 0}`)

    // Initialize local data with props
    payments.value = props.payments || []
    requirements.value = props.requirements || []
    users.value = props.users || []
})
</script>

<template>
    <div class="p-6 text-foreground">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-2xl font-bold">Payment Records</h2>
                <p class="text-muted-foreground">Manage and track all payment transactions</p>
            </div>
            <div class="flex gap-2">
                <Button @click="toggleSelectionMode" :variant="isSelectionMode ? 'default' : 'outline'" class="gap-2">
                    <Download class="w-4 h-4" /> Download
                </Button>
                <Button @click="openAddModal" class="gap-2">
                    <Plus class="w-4 h-4" /> Add Record
                </Button>
            </div>
        </div>

        <!-- Loading State - Removed since parent handles loading -->

        <!-- Content -->
        <div>
            <!-- Search + Filter -->
            <div class="flex items-center gap-3 mb-6">
                <!-- Search -->
                <div class="flex items-center flex-1 bg-muted border border-border rounded-lg px-3 py-2">
                    <Search class="w-4 h-4 text-muted-foreground" />
                    <input v-model="search" type="text" placeholder="Search name..."
                        class="ml-2 flex-1 bg-transparent text-sm focus:outline-none placeholder:text-muted-foreground" />
                </div>

                <!-- Filter -->
                <div class="relative">
                    <Button @click="isFilterOpen = !isFilterOpen" variant="outline" class="gap-2">
                        <Filter class="w-4 h-4" />
                        <span>{{ paymentFilter }}</span>
                        <ChevronDown class="w-4 h-4 transition-transform" :class="{ 'rotate-180': isFilterOpen }" />
                    </Button>

                    <!-- Dropdown -->
                    <div v-if="isFilterOpen"
                        class="absolute right-0 mt-2 w-32 bg-card border border-border rounded-lg shadow-lg z-10 overflow-hidden">
                        <button v-for="option in filterOptions" :key="option" @click="
                            paymentFilter = option;
                        isFilterOpen = false
                            " class="w-full text-left px-3 py-2 text-sm hover:bg-muted"
                            :class="{ 'bg-muted': paymentFilter === option }">
                            {{ option }}
                        </button>
                    </div>
                </div>

                <!-- Clear Filter -->
                <Button v-if="paymentFilter !== 'All' || search" @click="clearFilters" variant="outline">
                    Clear Filters
                </Button>
            </div>

            <!-- Selection Mode Controls -->
            <div v-if="isSelectionMode"
                class="flex items-center gap-3 mb-6 p-4 bg-blue-500/10 rounded-lg border border-blue-500/20">
                <span class="text-sm font-medium">
                    {{ selectedRecords.size }} record{{ selectedRecords.size !== 1 ? 's' : '' }} selected
                </span>
                <div class="flex-1"></div>
                <Button @click="downloadSelectedRecords" :disabled="!isAnySelected" class="gap-2">
                    <Download class="w-4 h-4" /> Download Selected
                </Button>
                <Button @click="toggleSelectionMode" variant="outline" class="gap-2">
                    <X class="w-4 h-4" /> Cancel
                </Button>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto rounded-xl border border-border bg-card">
                <table class="w-full text-sm text-left">
                    <thead class="bg-muted uppercase text-xs">
                        <tr>
                            <th v-if="isSelectionMode" class="px-4 py-3 text-muted-foreground w-12">
                                <input type="checkbox" :checked="isAllSelected" @change="toggleSelectAll"
                                    class="w-4 h-4 cursor-pointer rounded border-border" />
                            </th>
                            <th class="px-4 py-3 text-muted-foreground">Student</th>
                            <th class="px-4 py-3 text-muted-foreground">Requirement</th>
                            <th class="px-4 py-3 text-muted-foreground">Amount</th>
                            <th class="px-4 py-3 text-muted-foreground">Date Paid</th>
                            <th class="px-4 py-3 text-muted-foreground">Method</th>
                            <th class="px-4 py-3 text-muted-foreground">Status</th>
                            <th class="px-4 py-3 text-muted-foreground">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="record in filteredRecords" :key="record.id"
                            :class="['border-t border-border transition-colors', isSelectionMode && selectedRecords.has(record.id) ? 'bg-blue-500/10' : 'hover:bg-muted/50']">
                            <td v-if="isSelectionMode" class="px-4 py-3 w-12">
                                <input type="checkbox" :checked="selectedRecords.has(record.id)"
                                    @change="toggleRecordSelection(record.id)"
                                    class="w-4 h-4 cursor-pointer rounded border-border" />
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium">{{ getDisplayName(record) }}</div>
                                <div class="text-xs text-muted-foreground">{{ getUserDetails(record) }}</div>
                            </td>
                            <td class="px-4 py-3">{{ record.requirement.title }}</td>
                            <td class="px-4 py-3">₱{{ record.amount_paid }}</td>
                            <td class="px-4 py-3">{{ formatDate(record.paid_at) }}</td>
                            <td class="px-4 py-3">{{ record.payment_method || "—" }}</td>
                            <td class="px-4 py-3">
                                <Badge :class="{
                                    'bg-green-100 text-green-800 hover:bg-green-100 dark:bg-green-900 dark:text-green-300': record.status === 'paid',
                                    'bg-red-100 text-red-800 hover:bg-red-100 dark:bg-red-900 dark:text-red-300': record.status === 'unpaid',
                                    'bg-yellow-100 text-yellow-800 hover:bg-yellow-100 dark:bg-yellow-900 dark:text-yellow-300': record.status === 'pending',
                                }">
                                    {{ record.status }}
                                </Badge>
                            </td>
                            <td class="px-4 py-3 flex gap-2">
                                <Button @click="openEditModal(record)" variant="ghost" size="icon">
                                    <Pencil class="w-4 h-4" />
                                </Button>
                                <Button @click="deleteRecord(record.id)" variant="ghost" size="icon">
                                    <Trash2 class="w-4 h-4" />
                                </Button>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Empty state -->
                <div v-if="filteredRecords.length === 0" class="text-center py-8 text-muted-foreground">
                    <Search class="h-12 w-12 mx-auto mb-4 opacity-50" />
                    <p>No payment records found</p>
                    <Button v-if="!search && paymentFilter === 'All'" @click="openAddModal" class="gap-2 mt-4">
                        <Plus class="h-4 w-4" />
                        Add First Record
                    </Button>
                </div>
            </div>

            <!-- Add/Edit Record Modal -->
            <div v-if="showModal"
                class="fixed inset-0 flex items-center justify-center bg-background/80 backdrop-blur-sm z-50">
                <div
                    class="bg-card border border-border rounded-xl p-6 w-full max-w-md shadow-lg max-h-[90vh] overflow-y-auto">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">
                            {{ editingRecord ? "Edit Payment Record" : "Add Payment Record" }}
                        </h3>
                        <Button @click="showModal = false" variant="ghost" size="icon">
                            <X class="w-5 h-5" />
                        </Button>
                    </div>

                    <!-- Entry Type Toggle -->
                    <div class="mb-4 p-3 bg-muted rounded-lg">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium">Entry Type:</span>
                            <Button @click="toggleManualEntry" variant="outline" size="sm" class="gap-2">
                                <component :is="manualEntry ? User : UserPlus" class="w-4 h-4" />
                                {{ manualEntry ? "Manual Entry" : "Existing User" }}
                            </Button>
                        </div>
                        <p class="text-xs text-muted-foreground mt-1">
                            {{ manualEntry ? "Enter student details manually" : "Select from registered users" }}
                        </p>
                    </div>

                    <form @submit.prevent="saveRecord" class="space-y-4">
                        <!-- Existing User Selection -->
                        <div v-if="!manualEntry">
                            <label class="block text-sm text-muted-foreground mb-1">
                                Select Student <span class="text-destructive">*</span>
                            </label>
                            <select v-model="form.user_id"
                                class="w-full px-3 py-2 bg-background border border-border rounded-lg text-sm">
                                <option disabled value="">Select student</option>
                                <option v-for="user in users" :key="user.id" :value="user.id">
                                    {{ getUserDisplayName(user) }}
                                    {{ user.student_id ? `(${user.student_id})` : '' }}
                                </option>
                            </select>
                            <p v-if="users.length === 0" class="text-xs text-destructive mt-1">
                                No active users found in the system.
                            </p>
                        </div>

                        <!-- Manual Entry Fields -->
                        <div v-if="manualEntry" class="space-y-3 p-3 bg-muted/30 rounded-lg">
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm text-muted-foreground mb-1">
                                        First Name <span class="text-destructive">*</span>
                                    </label>
                                    <input v-model="form.first_name" type="text"
                                        class="w-full px-3 py-2 bg-background border border-border rounded-lg text-sm"
                                        placeholder="First name" />
                                </div>
                                <div>
                                    <label class="block text-sm text-muted-foreground mb-1">Middle Name</label>
                                    <input v-model="form.middle_name" type="text"
                                        class="w-full px-3 py-2 bg-background border border-border rounded-lg text-sm"
                                        placeholder="Middle name" />
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm text-muted-foreground mb-1">
                                        Last Name <span class="text-destructive">*</span>
                                    </label>
                                    <input v-model="form.last_name" type="text"
                                        class="w-full px-3 py-2 bg-background border border-border rounded-lg text-sm"
                                        placeholder="Last name" />
                                </div>
                                <div>
                                    <label class="block text-sm text-muted-foreground mb-1">Student ID</label>
                                    <input v-model="form.student_id" type="text"
                                        class="w-full px-3 py-2 bg-background border border-border rounded-lg text-sm"
                                        placeholder="Optional" />
                                </div>
                            </div>
                        </div>

                        <!-- Requirement Selection -->
                        <div>
                            <label class="block text-sm text-muted-foreground mb-1">
                                Requirement <span class="text-destructive">*</span>
                            </label>
                            <select v-model="form.requirement_id" @change="onRequirementChange"
                                class="w-full px-3 py-2 bg-background border border-border rounded-lg text-sm">
                                <option disabled value="">Select requirement</option>
                                <option v-for="req in requirements" :key="req.id" :value="req.id">
                                    {{ req.title }} (₱{{ req.amount }})
                                </option>
                            </select>
                            <p v-if="requirements.length === 0" class="text-xs text-destructive mt-1">
                                No requirements found. Please create requirements first.
                            </p>
                        </div>

                        <!-- Amount -->
                        <div>
                            <label class="block text-sm text-muted-foreground mb-1">
                                Amount <span class="text-destructive">*</span>
                            </label>
                            <input v-model.number="form.amount_paid" type="number" step="0.01"
                                class="w-full px-3 py-2 bg-background border border-border rounded-lg text-sm"
                                :class="{ 'bg-muted/50': selectedRequirement }" :readonly="!!selectedRequirement" />
                            <p v-if="selectedRequirement" class="text-xs text-muted-foreground mt-1">
                                Amount auto-filled from selected requirement
                            </p>
                        </div>

                        <!-- Status -->
                        <div>
                            <label class="block text-sm text-muted-foreground mb-1">
                                Status <span class="text-destructive">*</span>
                            </label>
                            <select v-model="form.status"
                                class="w-full px-3 py-2 bg-background border border-border rounded-lg text-sm">
                                <option value="pending">Pending</option>
                                <option value="paid">Paid</option>
                                <option value="unpaid">Unpaid</option>
                            </select>
                        </div>

                        <!-- Payment Method (show only if paid) -->
                        <div v-if="form.status === 'paid'">
                            <label class="block text-sm text-muted-foreground mb-1">Payment Method</label>
                            <select v-model="form.payment_method"
                                class="w-full px-3 py-2 bg-background border border-border rounded-lg text-sm">
                                <option value="Cash">Cash</option>
                                <option value="GCash">GCash</option>
                                <option value="PayMaya">PayMaya</option>
                                <option value="Bank Transfer">Bank Transfer</option>
                            </select>
                        </div>

                        <!-- Date Paid (show only if paid) -->
                        <div v-if="form.status === 'paid'">
                            <label class="block text-sm text-muted-foreground mb-1">Date Paid</label>
                            <input v-model="form.paid_at" type="date"
                                class="w-full px-3 py-2 bg-background border border-border rounded-lg text-sm" />
                        </div>

                        <!-- Notes -->
                        <div>
                            <label class="block text-sm text-muted-foreground mb-1">Notes</label>
                            <textarea v-model="form.notes"
                                class="w-full px-3 py-2 bg-background border border-border rounded-lg text-sm" rows="2"
                                placeholder="Additional notes..."></textarea>
                        </div>

                        <div class="flex justify-end gap-2 mt-6">
                            <Button type="button" @click="showModal = false" variant="outline">
                                Cancel
                            </Button>
                            <Button type="submit">
                                {{ editingRecord ? "Save Changes" : "Add Record" }}
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>