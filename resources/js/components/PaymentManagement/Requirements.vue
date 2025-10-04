<script setup lang="ts">
import { ref, computed, onMounted, watch } from "vue"
import { router } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Progress } from '@/components/ui/progress'

import {
    Search,
    Plus,
    Filter,
    LayoutGrid,
    List,
    ChevronDown,
    Pencil,
    Trash2,
    Calendar,
    Users,
    DollarSign,
    CheckCircle,
    Clock,
    AlertTriangle,
    X,
} from "lucide-vue-next"

// --- Types ---
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

// --- Props & Emits ---
const props = defineProps<{
    requirements: Requirement[]
}>()

const emit = defineEmits<{
    (e: 'refresh-data'): void
}>()

// --- State ---
const showModal = ref(false)
const editingRequirement = ref<Requirement | null>(null)
const isLoading = ref(false)

const search = ref("")
const isFilterOpen = ref(false)
const isGridView = ref(true)
const requirementFilter = ref("All")

const filterOptions = ["All", "Pending", "Overdue", "Done"]

// Pagination
const rowsPerPage = ref(10)
const currentPage = ref(1)

// Use props data directly
const requirements = ref<Requirement[]>(props.requirements || [])

// --- Modal Form Data ---
const form = ref({
    title: "",
    description: "",
    amount: 0,
    deadline: "",
    total_users: 0,
})

// Watch for prop changes and update local data
watch(() => props.requirements, (newRequirements) => {
    requirements.value = newRequirements || []
    console.log(`Updated requirements from props: ${requirements.value.length} records`)
})

// --- Methods ---
function openAddModal() {
    editingRequirement.value = null
    form.value = {
        title: "",
        description: "",
        amount: 0,
        deadline: "",
        total_users: 0,
    }
    showModal.value = true
}

function openEditModal(req: Requirement) {
    editingRequirement.value = req
    form.value = {
        title: req.title,
        description: req.description,
        amount: req.amount,
        deadline: req.deadline.split('T')[0], // Format date for input
        total_users: req.total_users,
    }
    showModal.value = true
}

function submitForm() {
    if (editingRequirement.value) {
        router.put(`/requirements/${editingRequirement.value.id}`, form.value, {
            onSuccess: () => {
                showModal.value = false
                resetForm()
                emit('refresh-data') // Emit event to parent to refresh data
            },
        })
    } else {
        router.post('/requirements', form.value, {
            onSuccess: () => {
                showModal.value = false
                resetForm()
                emit('refresh-data') // Emit event to parent to refresh data
            },
        })
    }
}

function deleteRequirement(id: number) {
    if (confirm('Are you sure you want to delete this requirement?')) {
        router.delete(`/requirements/${id}`, {
            onSuccess: () => {
                emit('refresh-data') // Emit event to parent to refresh data
            },
        })
    }
}

function resetForm() {
    form.value = {
        title: "",
        description: "",
        amount: 0,
        deadline: "",
        total_users: 0,
    }
    editingRequirement.value = null
}

function clearFilters() {
    requirementFilter.value = 'All'
    search.value = ''
}

// --- Status Logic ---
function getStatus(req: Requirement): "Pending" | "Overdue" | "Done" {
    const now = new Date()
    const deadline = new Date(req.deadline)

    if (req.paid < req.total_users) {
        return deadline >= now ? "Pending" : "Overdue"
    }
    return "Done"
}

function getStatusVariant(status: string) {
    switch (status) {
        case "Pending": return "outline"
        case "Overdue": return "destructive"
        case "Done": return "default"
        default: return "secondary"
    }
}

function getStatusIcon(status: string) {
    switch (status) {
        case "Pending": return Clock
        case "Overdue": return AlertTriangle
        case "Done": return CheckCircle
        default: return Clock
    }
}

// --- Filtered + Paginated List ---
const filteredRequirements = computed(() => {
    return requirements.value.filter((req: Requirement) => {
        const status = getStatus(req)
        const matchesSearch = req.title
            .toLowerCase()
            .includes(search.value.toLowerCase())

        if (requirementFilter.value === "All") return matchesSearch
        return status === requirementFilter.value && matchesSearch
    })
})

const paginatedRequirements = computed(() => {
    const start = (currentPage.value - 1) * rowsPerPage.value
    return filteredRequirements.value.slice(
        start,
        start + rowsPerPage.value
    )
})

const totalPages = computed(() => {
    return Math.ceil(filteredRequirements.value.length / rowsPerPage.value)
})

// Initialize with props when component mounts
onMounted(() => {
    console.log("Requirements component mounted")
    console.log(`Initial props - Requirements: ${props.requirements?.length || 0}`)
    
    // Initialize local data with props
    requirements.value = props.requirements || []
})
</script>

<template>
    <div class="text-foreground">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-2xl font-bold">Payment Requirements</h2>
                <p class="text-muted-foreground">Manage and track all payment requirements</p>
            </div>
            <div class="flex gap-2">
                <Button
                    @click="isGridView = !isGridView"
                    variant="outline"
                    size="icon"
                >
                    <component :is="isGridView ? List : LayoutGrid" class="w-4 h-4" />
                </Button>
                <Button
                    @click="openAddModal"
                    class="gap-2"
                >
                    <Plus class="w-4 h-4" /> Add Requirement
                </Button>
            </div>
        </div>

        <!-- Content -->
        <div>
            <!-- Search + Filter -->
            <div class="flex items-center gap-3 mb-6">
                <!-- Search -->
                <div class="flex items-center flex-1 bg-muted border border-border rounded-lg px-3 py-2">
                    <Search class="w-4 h-4 text-muted-foreground" />
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search requirements..."
                        class="ml-2 flex-1 bg-transparent text-sm focus:outline-none placeholder:text-muted-foreground"
                    />
                </div>

                <!-- Filter -->
                <div class="relative">
                    <Button
                        @click="isFilterOpen = !isFilterOpen"
                        variant="outline"
                        class="gap-2"
                    >
                        <Filter class="w-4 h-4" />
                        <span>{{ requirementFilter }}</span>
                        <ChevronDown
                            class="w-4 h-4 transition-transform"
                            :class="{ 'rotate-180': isFilterOpen }"
                        />
                    </Button>

                    <!-- Dropdown -->
                    <div
                        v-if="isFilterOpen"
                        class="absolute right-0 mt-2 w-32 bg-card border border-border rounded-lg shadow-lg z-10 overflow-hidden"
                    >
                        <button
                            v-for="option in filterOptions"
                            :key="option"
                            @click="
                                requirementFilter = option;
                                isFilterOpen = false
                            "
                            class="w-full text-left px-3 py-2 text-sm hover:bg-muted"
                            :class="{ 'bg-muted': requirementFilter === option }"
                        >
                            {{ option }}
                        </button>
                    </div>
                </div>

                <!-- Clear Filter -->
                <Button
                    v-if="requirementFilter !== 'All' || search"
                    @click="clearFilters"
                    variant="outline"
                >
                    Clear Filters
                </Button>
            </div>

            <!-- Grid View -->
            <div v-if="isGridView" class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <Card 
                    v-for="req in paginatedRequirements" 
                    :key="req.id" 
                    class="overflow-hidden border-border bg-card"
                >
                    <CardHeader class="pb-4">
                        <div class="flex items-start justify-between">
                            <div class="space-y-1">
                                <CardTitle class="text-lg">{{ req.title }}</CardTitle>
                                <CardDescription class="text-muted-foreground">{{ req.description }}</CardDescription>
                            </div>
                            <Badge 
                                :variant="getStatusVariant(getStatus(req))"
                                class="gap-1"
                            >
                                <component :is="getStatusIcon(getStatus(req))" class="h-3 w-3" />
                                {{ getStatus(req) }}
                            </Badge>
                        </div>
                    </CardHeader>
                    
                    <CardContent class="space-y-4">
                        <!-- Amount and Deadline -->
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div class="flex items-center gap-2 text-muted-foreground">
                                <DollarSign class="h-4 w-4" />
                                <span>₱{{ req.amount.toLocaleString() }}</span>
                            </div>
                            <div class="flex items-center gap-2 text-muted-foreground">
                                <Calendar class="h-4 w-4" />
                                <span>{{ new Date(req.deadline).toLocaleDateString() }}</span>
                            </div>
                        </div>

                        <!-- Progress -->
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-muted-foreground">Progress</span>
                                <span class="font-medium">{{ Math.round((req.paid / req.total_users) * 100) }}%</span>
                            </div>
                            <Progress 
                                :value="(req.paid / req.total_users) * 100" 
                                class="h-2"
                            />
                            <div class="flex justify-between text-xs text-muted-foreground">
                                <span>{{ req.paid }}/{{ req.total_users }} paid</span>
                                <span>{{ req.unpaid }} remaining</span>
                            </div>
                        </div>

                        <!-- Stats Grid -->
                        <div class="grid grid-cols-3 gap-3">
                            <div class="rounded-lg bg-blue-50 dark:bg-blue-950 p-3 text-center border border-blue-200 dark:border-blue-800">
                                <div class="flex items-center justify-center gap-1 text-blue-600 dark:text-blue-400">
                                    <Users class="h-4 w-4" />
                                    <span class="font-semibold text-lg">{{ req.total_users }}</span>
                                </div>
                                <p class="text-xs text-muted-foreground">Total</p>
                            </div>
                            <div class="rounded-lg bg-green-50 dark:bg-green-950 p-3 text-center border border-green-200 dark:border-green-800">
                                <div class="flex items-center justify-center gap-1 text-green-600 dark:text-green-400">
                                    <CheckCircle class="h-4 w-4" />
                                    <span class="font-semibold text-lg">{{ req.paid }}</span>
                                </div>
                                <p class="text-xs text-muted-foreground">Paid</p>
                            </div>
                            <div class="rounded-lg bg-red-50 dark:bg-red-950 p-3 text-center border border-red-200 dark:border-red-800">
                                <div class="flex items-center justify-center gap-1 text-red-600 dark:text-red-400">
                                    <Clock class="h-4 w-4" />
                                    <span class="font-semibold text-lg">{{ req.unpaid }}</span>
                                </div>
                                <p class="text-xs text-muted-foreground">Unpaid</p>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex justify-end gap-2 pt-2">
                            <Button 
                                @click="openEditModal(req)"
                                variant="outline"
                                size="sm"
                                class="gap-2"
                            >
                                <Pencil class="h-4 w-4" />
                                Edit
                            </Button>
                            <Button 
                                @click="deleteRequirement(req.id)"
                                variant="destructive"
                                size="sm"
                                class="gap-2"
                            >
                                <Trash2 class="h-4 w-4" />
                                Delete
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- List View -->
            <div v-else class="overflow-x-auto rounded-xl border border-border bg-card">
                <table class="w-full text-sm text-left">
                    <thead class="bg-muted uppercase text-xs">
                        <tr>
                            <th class="px-4 py-3 text-muted-foreground">Title</th>
                            <th class="px-4 py-3 text-muted-foreground">Amount</th>
                            <th class="px-4 py-3 text-muted-foreground">Deadline</th>
                            <th class="px-4 py-3 text-muted-foreground">Progress</th>
                            <th class="px-4 py-3 text-muted-foreground">Total</th>
                            <th class="px-4 py-3 text-muted-foreground">Paid</th>
                            <th class="px-4 py-3 text-muted-foreground">Unpaid</th>
                            <th class="px-4 py-3 text-muted-foreground">Status</th>
                            <th class="px-4 py-3 text-muted-foreground">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr 
                            v-for="req in paginatedRequirements" 
                            :key="req.id"
                            class="border-t border-border hover:bg-muted/50 transition-colors"
                        >
                            <td class="px-4 py-3">
                                <div>
                                    <p class="font-medium">{{ req.title }}</p>
                                    <p class="text-xs text-muted-foreground">{{ req.description }}</p>
                                </div>
                            </td>
                            <td class="px-4 py-3 font-medium">₱{{ req.amount.toLocaleString() }}</td>
                            <td class="px-4 py-3 text-sm">{{ new Date(req.deadline).toLocaleDateString() }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2 min-w-[120px]">
                                    <Progress 
                                        :value="(req.paid / req.total_users) * 100" 
                                        class="flex-1 h-2"
                                    />
                                    <span class="text-xs text-muted-foreground whitespace-nowrap">
                                        {{ req.paid }}/{{ req.total_users }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-blue-600 dark:text-blue-400 font-medium">{{ req.total_users }}</td>
                            <td class="px-4 py-3 text-green-600 dark:text-green-400 font-medium">{{ req.paid }}</td>
                            <td class="px-4 py-3 text-red-600 dark:text-red-400 font-medium">{{ req.unpaid }}</td>
                            <td class="px-4 py-3">
                                <Badge 
                                    :variant="getStatusVariant(getStatus(req))"
                                    class="gap-1"
                                >
                                    <component :is="getStatusIcon(getStatus(req))" class="h-3 w-3" />
                                    {{ getStatus(req) }}
                                </Badge>
                            </td>
                            <td class="px-4 py-3 flex gap-2">
                                <Button
                                    @click="openEditModal(req)"
                                    variant="ghost"
                                    size="icon"
                                >
                                    <Pencil class="w-4 h-4" />
                                </Button>
                                <Button
                                    @click="deleteRequirement(req.id)"
                                    variant="ghost"
                                    size="icon"
                                >
                                    <Trash2 class="w-4 h-4" />
                                </Button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Empty State -->
            <div v-if="filteredRequirements.length === 0" class="text-center py-8 text-muted-foreground">
                <DollarSign class="h-12 w-12 mx-auto mb-4 opacity-50" />
                <p>No payment requirements found</p>
                <Button 
                    v-if="!search && requirementFilter === 'All'" 
                    @click="openAddModal" 
                    class="gap-2 mt-4"
                >
                    <Plus class="h-4 w-4" />
                    Add First Requirement
                </Button>
            </div>

            <!-- Add/Edit Modal -->
            <div
                v-if="showModal"
                class="fixed inset-0 flex items-center justify-center bg-background/80 backdrop-blur-sm z-50"
            >
                <div class="bg-card border border-border rounded-xl p-6 w-full max-w-md shadow-lg">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">
                            {{ editingRequirement ? "Edit Requirement" : "Add Requirement" }}
                        </h3>
                        <Button @click="showModal = false" variant="ghost" size="icon">
                            <X class="w-5 h-5" />
                        </Button>
                    </div>
                    <form @submit.prevent="submitForm" class="space-y-4">
                        <div>
                            <label class="block text-sm text-muted-foreground mb-1">Title</label>
                            <input
                                v-model="form.title"
                                type="text"
                                class="w-full px-3 py-2 bg-background border border-border rounded-lg text-sm"
                                placeholder="Enter requirement title"
                                required
                            />
                        </div>
                        <div>
                            <label class="block text-sm text-muted-foreground mb-1">Description</label>
                            <textarea
                                v-model="form.description"
                                rows="3"
                                class="w-full px-3 py-2 bg-background border border-border rounded-lg text-sm resize-none"
                                placeholder="Enter requirement description"
                            />
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm text-muted-foreground mb-1">Amount (₱)</label>
                                <input
                                    v-model.number="form.amount"
                                    type="number"
                                    class="w-full px-3 py-2 bg-background border border-border rounded-lg text-sm"
                                    placeholder="0"
                                    min="0"
                                    step="0.01"
                                    required
                                />
                            </div>
                            <div>
                                <label class="block text-sm text-muted-foreground mb-1">Total Users</label>
                                <input
                                    v-model.number="form.total_users"
                                    type="number"
                                    class="w-full px-3 py-2 bg-background border border-border rounded-lg text-sm"
                                    placeholder="0"
                                    min="1"
                                    required
                                />
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm text-muted-foreground mb-1">Deadline</label>
                            <input
                                v-model="form.deadline"
                                type="date"
                                class="w-full px-3 py-2 bg-background border border-border rounded-lg text-sm"
                                required
                            />
                        </div>

                        <div class="flex justify-end gap-2 mt-6">
                            <Button
                                type="button"
                                @click="showModal = false"
                                variant="outline"
                            >
                                Cancel
                            </Button>
                            <Button
                                type="submit"
                            >
                                {{ editingRequirement ? "Save Changes" : "Add" }}
                            </Button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Pagination Footer -->
            <div v-if="filteredRequirements.length > 0" class="flex items-center justify-between p-4 text-sm text-muted-foreground mt-4">
                <span>
                    Showing {{ ((currentPage - 1) * rowsPerPage) + 1 }} to 
                    {{ Math.min(currentPage * rowsPerPage, filteredRequirements.length) }} of 
                    {{ filteredRequirements.length }} requirements (page {{ currentPage }} of {{ totalPages }})
                </span>

                <div class="flex items-center gap-2">
                    <span>Rows per page:</span>
                    <select
                        v-model="rowsPerPage"
                        class="bg-background border border-border rounded px-2 py-1"
                    >
                        <option>10</option>
                        <option>25</option>
                        <option>50</option>
                    </select>
                    <Button
                        @click="currentPage--"
                        :disabled="currentPage <= 1"
                        variant="outline"
                        size="sm"
                    >
                        Previous
                    </Button>
                    <Button 
                        v-for="page in Math.min(totalPages, 5)" 
                        :key="page"
                        @click="currentPage = page"
                        :variant="currentPage === page ? 'default' : 'outline'"
                        size="sm"
                    >
                        {{ page }}
                    </Button>
                    <Button
                        @click="currentPage++"
                        :disabled="currentPage >= totalPages"
                        variant="outline"
                        size="sm"
                    >
                        Next
                    </Button>
                </div>
            </div>
        </div>
    </div>
</template>