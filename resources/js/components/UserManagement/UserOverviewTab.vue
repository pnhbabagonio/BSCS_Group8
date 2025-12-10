<script setup lang="ts">
import { computed } from 'vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import {
    Users,
    UserCheck,
    UserX,
    Shield,
    UserCog,
    User,
    Plus,
    Download,
    Calendar
} from "lucide-vue-next"

interface User {
    id: number
    name: string
    email: string
    student_id: string
    program: string
    year: string
    role: 'Member' | 'Officer' | 'Admin'
    status: 'active' | 'inactive'
    last_login: string
}

interface UserStats {
    total: number
    active: number
    inactive: number
    admins: number
    officers: number
    members: number
}

const props = defineProps<{
    users: User[]
    stats: UserStats
}>()

const emit = defineEmits<{
    switchToUsers: []
    switchToAdd: []
}>()

// Recent users (last 5)
const recentUsers = computed(() => {
    return props.users.slice(0, 5)
})

// Export user data as CSV
const exportUserData = () => {
    if (!props.users || props.users.length === 0) {
        alert('No users to export')
        return
    }

    // CSV header
    const headers = ['ID', 'Name', 'Email', 'Student ID', 'Program', 'Year', 'Role', 'Status', 'Last Login']

    // CSV rows
    const rows = props.users.map(user => [
        user.id,
        `"${user.name}"`, // Quote to handle commas in names
        user.email,
        user.student_id || '',
        user.program || '',
        user.year || '',
        user.role,
        user.status,
        user.last_login
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
    link.setAttribute('download', `users_export_${new Date().toISOString().split('T')[0]}.csv`)
    link.style.visibility = 'hidden'

    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
}

// Role distribution for chart
const roleDistribution = computed(() => {
    const total = props.stats.total
    return [
        { name: 'Members', value: props.stats.members, percentage: total ? Math.round((props.stats.members / total) * 100) : 0, color: 'bg-green-500' },
        { name: 'Officers', value: props.stats.officers, percentage: total ? Math.round((props.stats.officers / total) * 100) : 0, color: 'bg-blue-500' },
        { name: 'Admins', value: props.stats.admins, percentage: total ? Math.round((props.stats.admins / total) * 100) : 0, color: 'bg-red-500' },
    ]
})

const getRoleVariant = (role: string) => {
    switch (role) {
        case 'Admin': return 'destructive'
        case 'Officer': return 'default'
        case 'Member': return 'secondary'
        default: return 'outline'
    }
}

const getStatusVariant = (status: string) => {
    return status === 'active' ? 'default' : 'destructive'
}
</script>

<template>
    <div class="space-y-6">
        <!-- Stats Cards -->
        <div class="grid grid-cols-2 md:grid-cols-6 gap-4">
            <!-- Total Users -->
            <Card>
                <CardContent class="p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Total Users</p>
                            <p class="text-2xl font-bold text-foreground">{{ stats.total }}</p>
                        </div>
                        <div class="w-8 h-8 bg-primary/10 rounded-full flex items-center justify-center">
                            <Users class="h-4 w-4 text-primary" />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Active Users -->
            <Card>
                <CardContent class="p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Active</p>
                            <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ stats.active }}</p>
                        </div>
                        <div class="w-8 h-8 bg-green-500/10 rounded-full flex items-center justify-center">
                            <UserCheck class="h-4 w-4 text-green-600 dark:text-green-400" />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Inactive Users -->
            <Card>
                <CardContent class="p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Inactive</p>
                            <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ stats.inactive }}</p>
                        </div>
                        <div class="w-8 h-8 bg-red-500/10 rounded-full flex items-center justify-center">
                            <UserX class="h-4 w-4 text-red-600 dark:text-red-400" />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Admins -->
            <Card>
                <CardContent class="p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Admins</p>
                            <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ stats.admins }}</p>
                        </div>
                        <div class="w-8 h-8 bg-red-500/10 rounded-full flex items-center justify-center">
                            <Shield class="h-4 w-4 text-red-600 dark:text-red-400" />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Officers -->
            <Card>
                <CardContent class="p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Officers</p>
                            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ stats.officers }}</p>
                        </div>
                        <div class="w-8 h-8 bg-blue-500/10 rounded-full flex items-center justify-center">
                            <UserCog class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Members -->
            <Card>
                <CardContent class="p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Members</p>
                            <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ stats.members }}</p>
                        </div>
                        <div class="w-8 h-8 bg-green-500/10 rounded-full flex items-center justify-center">
                            <User class="h-4 w-4 text-green-600 dark:text-green-400" />
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Content Grid -->
        <div class="grid md:grid-cols-2 gap-6">
            <!-- Role Distribution -->
            <Card>
                <CardHeader class="pb-4">
                    <CardTitle class="text-lg">Role Distribution</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="space-y-3">
                        <div v-for="role in roleDistribution" :key="role.name"
                            class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-3 h-3 rounded-full" :class="role.color"></div>
                                <span class="text-sm text-foreground">{{ role.name }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-medium text-foreground">{{ role.value }}</span>
                                <span class="text-xs text-muted-foreground">({{ role.percentage }}%)</span>
                            </div>
                        </div>
                    </div>
                    <div class="h-2 bg-muted rounded-full overflow-hidden">
                        <div class="h-full flex">
                            <div v-for="role in roleDistribution" :key="role.name" :class="role.color"
                                :style="{ width: role.percentage + '%' }"></div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Quick Actions -->
            <Card>
                <CardHeader class="pb-4">
                    <CardTitle class="text-lg">Quick Actions</CardTitle>
                </CardHeader>
                <CardContent class="space-y-3">
                    <Button @click="emit('switchToAdd')" class="w-full justify-start gap-3 h-auto py-3">
                        <Plus class="h-4 w-4" />
                        Add New User
                    </Button>
                    <Button @click="emit('switchToUsers')" variant="outline"
                        class="w-full justify-start gap-3 h-auto py-3">
                        <Users class="h-4 w-4" />
                        View All Users
                    </Button>
                    <Button @click="exportUserData" variant="outline" class="w-full justify-start gap-3 h-auto py-3">
                        <Download class="h-4 w-4" />
                        Export User Data
                    </Button>
                </CardContent>
            </Card>
        </div>

        <!-- Recent Users -->
        <Card>
            <CardHeader class="flex flex-row items-center justify-between pb-4">
                <div>
                    <CardTitle class="text-lg">Recent Users</CardTitle>
                    <CardDescription>Recently added or active users</CardDescription>
                </div>
                <Button @click="emit('switchToUsers')" variant="ghost" size="sm" class="gap-2">
                    View All
                </Button>
            </CardHeader>
            <CardContent>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b">
                                <th class="text-left py-3 text-sm font-medium text-muted-foreground">Name</th>
                                <th class="text-left py-3 text-sm font-medium text-muted-foreground">Email</th>
                                <th class="text-left py-3 text-sm font-medium text-muted-foreground">Role</th>
                                <th class="text-left py-3 text-sm font-medium text-muted-foreground">Status</th>
                                <th class="text-left py-3 text-sm font-medium text-muted-foreground">Last Login</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="user in recentUsers" :key="user.id" class="border-b hover:bg-muted/50">
                                <td class="py-3">
                                    <div class="font-medium text-foreground">{{ user.name }}</div>
                                </td>
                                <td class="py-3">
                                    <div class="text-muted-foreground">{{ user.email }}</div>
                                </td>
                                <td class="py-3">
                                    <Badge :variant="getRoleVariant(user.role)">
                                        {{ user.role }}
                                    </Badge>
                                </td>
                                <td class="py-3">
                                    <Badge :variant="getStatusVariant(user.status)">
                                        {{ user.status }}
                                    </Badge>
                                </td>
                                <td class="py-3">
                                    <div class="flex items-center gap-2 text-muted-foreground">
                                        <Calendar class="h-3 w-3" />
                                        {{ user.last_login }}
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </CardContent>
        </Card>
    </div>
</template>