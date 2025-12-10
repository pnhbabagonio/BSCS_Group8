<!-- resources/js/Pages/SupportTickets.vue -->
<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
  Search,
  Filter,
  Download,
  Eye,
  MessageSquare,
  Calendar,
  User,
  Mail,
  AlertCircle,
  CheckCircle,
  Clock,
  XCircle,
  FileText,
} from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
  tickets: Array<{
    id: number;
    subject: string;
    message: string;
    category: string;
    priority: string;
    status: string;
    created_at: string;
    updated_at: string;
    user: {
      id: number;
      name: string;
      email: string;
    };
    attachments?: Array<{
      name: string;
      url: string;
    }>;
  }>;
  filters: {
    status: string;
    priority: string;
    category: string;
    search: string;
  };
}>();

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Support Tickets', href: '/help-support/tickets' },
];

// Local state
const expandedTickets = ref<number[]>([]);
const showFilters = ref(false);

// Filters
const filterStatus = ref(props.filters.status || '');
const filterPriority = ref(props.filters.priority || '');
const filterCategory = ref(props.filters.category || '');
const searchQuery = ref(props.filters.search || '');

// Apply filters
const applyFilters = () => {
  router.get('/help-support/tickets', {
    status: filterStatus.value,
    priority: filterPriority.value,
    category: filterCategory.value,
    search: searchQuery.value,
  }, {
    preserveState: true,
    preserveScroll: true,
  });
};

// Clear filters
const clearFilters = () => {
  filterStatus.value = '';
  filterPriority.value = '';
  filterCategory.value = '';
  searchQuery.value = '';
  applyFilters();
};

// Toggle ticket details
const toggleTicketDetails = (ticketId: number) => {
  const index = expandedTickets.value.indexOf(ticketId);
  if (index > -1) {
    expandedTickets.value.splice(index, 1);
  } else {
    expandedTickets.value.push(ticketId);
  }
};

// Get priority badge color
const getPriorityColor = (priority: string) => {
  switch (priority) {
    case 'low': return 'bg-blue-100 text-blue-800';
    case 'medium': return 'bg-yellow-100 text-yellow-800';
    case 'high': return 'bg-orange-100 text-orange-800';
    case 'urgent': return 'bg-red-100 text-red-800';
    default: return 'bg-gray-100 text-gray-800';
  }
};

// Get status badge color
const getStatusColor = (status: string) => {
  switch (status) {
    case 'open': return 'bg-green-100 text-green-800';
    case 'in_progress': return 'bg-blue-100 text-blue-800';
    case 'resolved': return 'bg-purple-100 text-purple-800';
    case 'closed': return 'bg-gray-100 text-gray-800';
    default: return 'bg-gray-100 text-gray-800';
  }
};

// Get status icon
const getStatusIcon = (status: string) => {
  switch (status) {
    case 'open': return CheckCircle;
    case 'in_progress': return Clock;
    case 'resolved': return CheckCircle;
    case 'closed': return XCircle;
    default: return AlertCircle;
  }
};

// Format date
const formatDate = (dateString: string) => {
  try {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    });
  } catch (e) {
    return dateString;
  }
};

// Export tickets to CSV
const exportToCSV = () => {
  // Create CSV content
  const headers = ['ID', 'Subject', 'Category', 'Priority', 'Status', 'User', 'Email', 'Created At', 'Updated At'];
  const csvContent = [
    headers.join(','),
    ...props.tickets.map(ticket => [
      ticket.id,
      `"${ticket.subject.replace(/"/g, '""')}"`,
      ticket.category,
      ticket.priority,
      ticket.status,
      `"${(ticket.user?.name || 'Unknown').replace(/"/g, '""')}"`,
      ticket.user?.email || 'No email',
      ticket.created_at,
      ticket.updated_at,
    ].join(','))
  ].join('\n');

  // Create blob and download
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
  const link = document.createElement('a');
  const url = URL.createObjectURL(blob);
  link.setAttribute('href', url);
  link.setAttribute('download', `support-tickets-${new Date().toISOString().split('T')[0]}.csv`);
  link.style.visibility = 'hidden';
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};
</script>

<template>

  <Head title="Support Tickets" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-6 p-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-foreground">Support Tickets</h1>
          <p class="text-muted-foreground">View and manage submitted support requests</p>
        </div>
        <div class="flex items-center gap-3">
          <Button variant="outline" @click="exportToCSV">
            <Download class="mr-2 h-4 w-4" />
            Export CSV
          </Button>
          <Button variant="outline" @click="showFilters = !showFilters">
            <Filter class="mr-2 h-4 w-4" />
            Filters
          </Button>
        </div>
      </div>

      <!-- Filters Panel -->
      <div v-if="showFilters" class="rounded-lg border border-border bg-card p-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <!-- Search -->
          <div>
            <label class="block text-sm font-medium mb-2">Search</label>
            <div class="relative">
              <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
              <input v-model="searchQuery" @keyup.enter="applyFilters" type="text" placeholder="Search tickets..."
                class="pl-10 w-full px-3 py-2 border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-card" />
            </div>
          </div>

          <!-- Status Filter -->
          <div>
            <label class="block text-sm font-medium mb-2">Status</label>
            <select v-model="filterStatus"
              class="w-full px-3 py-2 border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-card">
              <option value="">All Status</option>
              <option value="open">Open</option>
              <option value="in_progress">In Progress</option>
              <option value="resolved">Resolved</option>
              <option value="closed">Closed</option>
            </select>
          </div>

          <!-- Priority Filter -->
          <div>
            <label class="block text-sm font-medium mb-2">Priority</label>
            <select v-model="filterPriority"
              class="w-full px-3 py-2 border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-card">
              <option value="">All Priorities</option>
              <option value="low">Low</option>
              <option value="medium">Medium</option>
              <option value="high">High</option>
              <option value="urgent">Urgent</option>
            </select>
          </div>

          <!-- Category Filter -->
          <div>
            <label class="block text-sm font-medium mb-2">Category</label>
            <select v-model="filterCategory"
              class="w-full px-3 py-2 border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-card">
              <option value="">All Categories</option>
              <option value="technical">Technical</option>
              <option value="billing">Billing</option>
              <option value="account">Account</option>
              <option value="feature_request">Feature Request</option>
              <option value="other">Other</option>
            </select>
          </div>
        </div>

        <!-- Filter Actions -->
        <div class="flex justify-end gap-3 mt-4">
          <Button variant="outline" @click="clearFilters">
            Clear Filters
          </Button>
          <Button @click="applyFilters">
            Apply Filters
          </Button>
        </div>
      </div>

      <!-- Tickets Table -->
      <div class="rounded-lg border border-border bg-card overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="border-b border-border bg-muted/50">
                <th class="text-left py-3 px-4 font-medium text-sm">ID</th>
                <th class="text-left py-3 px-4 font-medium text-sm">Subject</th>
                <th class="text-left py-3 px-4 font-medium text-sm">User</th>
                <th class="text-left py-3 px-4 font-medium text-sm">Category</th>
                <th class="text-left py-3 px-4 font-medium text-sm">Priority</th>
                <th class="text-left py-3 px-4 font-medium text-sm">Status</th>
                <th class="text-left py-3 px-4 font-medium text-sm">Created</th>
                <th class="text-left py-3 px-4 font-medium text-sm">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="ticket in tickets" :key="ticket.id"
                class="border-b border-border hover:bg-muted/50 transition-colors">
                <td class="py-3 px-4 text-sm">#{{ ticket.id }}</td>
                <td class="py-3 px-4">
                  <div class="font-medium">{{ ticket.subject }}</div>
                  <div class="text-xs text-muted-foreground truncate max-w-xs">
                    {{ ticket.message?.substring(0, 50) || 'No message' }}...
                  </div>
                </td>
                <td class="py-3 px-4">
                  <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                      <User class="h-4 w-4 text-blue-600" />
                    </div>
                    <div>
                      <div class="font-medium text-sm">{{ ticket.user?.name || 'Unknown User' }}</div>
                      <div class="text-xs text-muted-foreground">{{ ticket.user?.email || 'No email' }}</div>
                    </div>
                  </div>
                </td>
                <td class="py-3 px-4">
                  <Badge variant="outline" class="capitalize">
                    {{ ticket.category?.replace('_', ' ') || 'Unknown' }}
                  </Badge>
                </td>
                <td class="py-3 px-4">
                  <Badge :class="getPriorityColor(ticket.priority)" class="capitalize">
                    {{ ticket.priority || 'medium' }}
                  </Badge>
                </td>
                <td class="py-3 px-4">
                  <Badge :class="getStatusColor(ticket.status)" class="capitalize">
                    <component :is="getStatusIcon(ticket.status)" class="mr-1 h-3 w-3" />
                    {{ ticket.status?.replace('_', ' ') || 'unknown' }}
                  </Badge>
                </td>
                <td class="py-3 px-4 text-sm text-muted-foreground">
                  {{ formatDate(ticket.created_at) }}
                </td>
                <td class="py-3 px-4">
                  <div class="flex items-center gap-2">
                    <Button size="sm" variant="ghost" @click="toggleTicketDetails(ticket.id)">
                      <Eye class="h-4 w-4" />
                    </Button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- No Tickets Message -->
        <div v-if="tickets.length === 0" class="py-12 text-center">
          <MessageSquare class="h-12 w-12 text-muted-foreground mx-auto mb-4" />
          <h3 class="text-lg font-medium mb-2">No support tickets found</h3>
          <p class="text-muted-foreground">No tickets match your current filters.</p>
        </div>
      </div>

      <!-- Ticket Details (Expanded) -->
      <div v-for="ticket in tickets" :key="ticket.id">
        <div v-if="expandedTickets.includes(ticket.id)" class="rounded-lg border border-border bg-card p-6 mt-2">
          <div class="flex justify-between items-start mb-6">
            <div>
              <h3 class="text-lg font-semibold mb-2">{{ ticket.subject }}</h3>
              <div class="flex items-center gap-4 text-sm text-muted-foreground">
                <div class="flex items-center gap-1">
                  <User class="h-4 w-4" />
                  {{ ticket.user?.name || 'Unknown User' }}
                </div>
                <div class="flex items-center gap-1">
                  <Mail class="h-4 w-4" />
                  {{ ticket.user?.email || 'No email' }}
                </div>
                <div class="flex items-center gap-1">
                  <Calendar class="h-4 w-4" />
                  {{ formatDate(ticket.created_at) }}
                </div>
              </div>
            </div>
            <div class="flex gap-2">
              <Badge :class="getPriorityColor(ticket.priority)" class="capitalize">
                {{ ticket.priority }}
              </Badge>
              <Badge :class="getStatusColor(ticket.status)" class="capitalize">
                <component :is="getStatusIcon(ticket.status)" class="mr-1 h-3 w-3" />
                {{ ticket.status?.replace('_', ' ') }}
              </Badge>
            </div>
          </div>

          <div class="space-y-6">
            <!-- Message -->
            <div>
              <h4 class="font-medium mb-2">Message</h4>
              <div class="p-4 bg-muted/30 rounded-lg whitespace-pre-wrap">
                {{ ticket.message }}
              </div>
            </div>

            <!-- Category & Details -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <h4 class="font-medium mb-2">Category</h4>
                <Badge variant="outline" class="capitalize">
                  {{ ticket.category?.replace('_', ' ') }}
                </Badge>
              </div>
              <div>
                <h4 class="font-medium mb-2">Last Updated</h4>
                <p class="text-sm text-muted-foreground">
                  {{ formatDate(ticket.updated_at) }}
                </p>
              </div>
            </div>

            <!-- Attachments -->
            <div v-if="ticket.attachments && ticket.attachments.length > 0">
              <h4 class="font-medium mb-2">Attachments</h4>
              <div class="flex flex-wrap gap-2">
                <a v-for="(attachment, index) in ticket.attachments" :key="index" :href="attachment.url" target="_blank"
                  class="inline-flex items-center gap-2 px-3 py-2 bg-muted/30 rounded-lg hover:bg-muted/50 transition-colors">
                  <FileText class="h-4 w-4" />
                  <span class="text-sm">{{ attachment.name }}</span>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>