<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Textarea } from '@/components/ui/textarea';
import InputError from '@/components/InputError.vue';
import Badge from '@/components/ui/badge/Badge.vue';
import { type BreadcrumbItem } from '@/types';
import { dashboard } from '@/routes';
import { Trash2, CheckCircle2, Circle, Search, X, Plus, Pencil, Loader2 } from 'lucide-vue-next';
import { ref } from 'vue';
import { watchDebounced } from '@vueuse/core';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Tasks',
                href: dashboard(),
            },
        ],
    },
});

interface Tasks {
    nTaskNo: number;
    cTaskName: string;
    cTasksDescription: string;
    cTaskPriority: 'Low' | 'Normal' | 'High';
    cCompleted: boolean;
    created_at: string;
    list: {
        nTaskListNo: number;
        cTaskListName: string;
        cTaskListsColor?: string;
    };
    nTaskListNo: number;
};

interface TasksList {
    nTaskListNo: number;
    cTaskListName: string;
    cTaskListsColor: string
};

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedTasks {
    data: Tasks[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: PaginationLink[];
};

const props = defineProps<{
    tasks: PaginatedTasks;
    lists: TasksList[];
    filters: {
        search?: string;
        cTaskPriority?: string;
        nTaskListNo?: number;
    }
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Tasks', href: '/tasks' }
];


//Filter State
const search = ref(props.filters.search || '');
const cTaskPriority = ref(props.filters.cTaskPriority || '');
const listId = ref(props.filters.nTaskListNo || '');



//Dialog state
const isCreateDialogOpen = ref(false);
const isEditDialogOpen = ref(false);
const editingTask = ref<Tasks | null>(null);

const deletingTaskId = ref<number | null>(null);

const createForm = useForm({
    cTaskName: '',
    cTasksDescription: '',
    cTaskPriority: 'Normal',
    nTaskListNo: listId.value || (props.lists.length > 0 ? props.lists[0].nTaskListNo : null)
});

const editForm = useForm({
    cTaskName: '',
    cTasksDescription: '',
    cTaskPriority: 'Normal',
    cCompleted: false,     
    nTaskListNo: null 
});


//Watch for filter changes and update URL with debounce
watchDebounced([search, cTaskPriority, listId], () => {
    router.get('/tasks', {
        search: search.value,
        cTaskPriority: cTaskPriority.value,
        nTaskListNo: listId.value
    }, { preserveState: true, preserveScroll: true });
}, { debounce: 500 });


const clearFilters = () => {
    search.value = '';
    cTaskPriority.value = '';
    listId.value = '';
}; 


const toggleTaskCompletion = (task: Tasks) => {
    router.put(`/tasks/${task.nTaskNo}`, {
        cTaskName: task.cTaskName,
        cTasksDescription: task.cTasksDescription,
        cTaskPriority: task.cTaskPriority,
        cCompleted: !task.cCompleted,
        nTaskListNo: task.nTaskListNo  // add this
    }, { preserveScroll: true });
};


const createTask = () => {
    createForm.post('/lists/tasks', {
        preserveScroll: true,
        onSuccess: () => {
            isCreateDialogOpen.value = false;
            createForm.reset();
        }
    });
};


const updateTask = () => {
    if (!editingTask.value) return;

    editForm.put(`/tasks/${editingTask.value.nTaskNo}`, {
        preserveScroll: true,
        onSuccess: () => {
            isEditDialogOpen.value = false;
            editForm.reset();
            editingTask.value = null;
        }
    });
};


const deleteTask = (taskId: number) => {
    if (confirm('Are you sure you want to delete this task?')) {
        deletingTaskId.value = taskId;
        router.delete(`/tasks/${taskId}`, {
            preserveScroll: true,
            onSuccess: () => {
                deletingTaskId.value = null;
            }
        });
    }
};


const openEditDialog = (task: Tasks) => {
    editingTask.value = { ...task };
    editForm.cTaskName = task.cTaskName;
    editForm.cTasksDescription = task.cTasksDescription;
    editForm.cTaskPriority = task.cTaskPriority;
    editForm.cCompleted = task.cCompleted;     
    nTaskListNo: null as number | null
    isEditDialogOpen.value = true;
};


const getPriorityVariant = (cTaskPriority: string): 'default' | 'secondary' | 'destructive' => {
    switch (cTaskPriority) {
        case 'High':
            return 'destructive';
        case 'Normal':
            return 'default';
        case 'Low':
            return 'secondary';
        default:
            return 'default';
    }
};
</script>


<template>
    <Head title="Tasks" />
        <div class="p-6 space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold">All Tasks</h1>
                    <p class="text-muted-foreground">Manage your tasks efficiently ({{ tasks.total }})</p>
                </div>

                <!-- Create Task Dialog -->
                 <Dialog v-model:open="isCreateDialogOpen">
                    <DialogTrigger as-child>
                        <Button>
                            <Plus class="h-4 w-4 mr-2"/>
                            Add Task
                        </Button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>Add New Task</DialogTitle>
                            <DialogDescription>
                                Create a new task and assign it to a list. Fill in the details and click "Create Task" to add it to your task board.
                            </DialogDescription>
                        </DialogHeader>
                        <form @submit.prevent="createTask" class="space-y-4">
                            <!-- Task Name -->
                            <div class="space-y-2">
                                <Label for="cTaskName">Task Name</Label>
                                <Input id="cTaskName" v-model="createForm.cTaskName" required placeholder="Enter task name" />
                                <InputError :message="createForm.errors.cTaskName" />
                            </div>
                            <!-- Task Name -->

                            <!-- List -->
                             <div class="space-y-2">
                                <Label for="nTaskListNo">Assign to List</Label>
                                <select id="nTaskListNo" v-model="createForm.nTaskListNo" required class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                                    <option value="" disabled>Select a list</option>
                                    <option v-for="list in lists" :key="list.nTaskListNo" :value="list.nTaskListNo">
                                        {{ list.cTaskListName }}
                                    </option>
                                </select>
                                <InputError :message="createForm.errors.nTaskListNo" />
                             </div>
                            <!-- List -->

                            <!-- Task Description -->
                             <div class="space-y-2">
                                <Label for="cTasksDescription">Task Description</Label>
                                <Textarea id="cTasksDescription" v-model="createForm.cTasksDescription" class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2" placeholder="Enter task description..." />
                                <InputError :message="createForm.errors.cTasksDescription" />
                             </div>
                             <!-- Task Description -->

                            <!-- Task Priority -->
                             <div class="space-y-2">
                                <Label for="cTaskPriority">Task Priority</Label>
                                <select id="cTaskPriority" v-model="createForm.cTaskPriority" required class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                                    <option value="Low">Low</option>
                                    <option value="Normal">Normal</option>
                                    <option value="High">High</option>
                                </select>
                                <InputError :message="createForm.errors.cTaskPriority" />
                            </div>
                            <!-- Task Priority -->

                            <!-- Task Submit -->
                            <Button type="submit" class="w-full" :disabled="createForm.processing">
                                <Loader2 v-if="createForm.processing" class="animate-spin h-4 w-4 mr-2"/>
                                {{ createForm.processing ? 'Creating...' : 'Create Task' }}
                            </Button>
                        </form>
                    </DialogContent>
                 </Dialog>


                 <!-- Edit Task Dialog -->
                 <Dialog v-model:open="isEditDialogOpen">
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>Edit Task</DialogTitle>
                            <DialogDescription>
                                Update the details of your task. Make the necessary changes and click "Update Task" to save the changes.
                            </DialogDescription>
                        </DialogHeader>
                        <form v-if="editingTask" @submit.prevent="updateTask" class="space-y-4">
                            <!-- Edit Task  Name -->
                            <div class="space-y-2">
                                <Label for="cEditTaskName">Task Name</Label>
                                <Input id="cEditTaskName" v-model="editForm.cTaskName" required placeholder="Enter task name" />
                                <InputError :message="editForm.errors.cTaskName" />
                            </div>
                            <!-- Edit Task  Name -->

                            <!-- Edit Task Description -->
                            <div class="space-y-2">
                                <Label for="cEditTaskDescription">Task Description</Label>
                                <Textarea id="cEditTaskDescription" v-model="editForm.cTasksDescription" class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2" placeholder="Enter task description..." />
                                <InputError :message="editForm.errors.cTasksDescription" />
                            </div>
                            <!-- Edit Task Description -->

                            <!-- Edit Task Priority -->
                            <div class="space-y-2">
                                <Label for="cEditTaskPriority">Task Priority</Label>
                                <select id="cEditTaskPriority" v-model="editForm.cTaskPriority" required class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                                    <option value="Low">Low</option>
                                    <option value="Normal">Normal</option>
                                    <option value="High">High</option>
                                </select>
                                <InputError :message="editForm.errors.cTaskPriority" />
                            </div>
                            <!-- Edit Task Priority -->

                            <!-- Edit Task Submit -->
                            <Button type="submit" class="w-full" :disabled="editForm.processing">
                                <Loader2 v-if="editForm.processing" class="animate-spin h-4 w-4 mr-2"/>
                                {{ editForm.processing ? 'Updating...' : 'Update Task' }}
                            </Button>

                        </form>
                    </DialogContent>
                 </Dialog>
            </div>


            <!-- Filters -->
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <CardTitle>Filters</CardTitle>
                        <Button variant="outline" size="sm" @click="clearFilters">
                            <X class="h-4 w-4 mr-2"/>
                            Clear Filters
                        </Button>
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-4 md:grid-cols-3">
                         <div class="space-y-2">
                            <Label for="search">Search</Label>
                            <div class="relative">
                                <Search class="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" />
                                <Input id="search" v-model="search" placeholder="Search tasks..." class="pl-8" />
                            </div>
                         </div>

                        <div class="space-y-2">
                            <Label for="list">List</Label>
                            <select id="list" v-model="listId" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                                <option value="">All Lists</option>
                                <option v-for="list in lists" :key="list.nTaskListNo" :value="list.nTaskListNo">
                                    {{ list.cTaskListName }}
                                </option>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <Label for="cTaskPriority">Priority</Label>
                            <select id="cTaskPriority" v-model="cTaskPriority" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                                <option value="">All Priorities</option>
                                <option value="Low">Low</option>
                                <option value="Normal">Normal</option>
                                <option value="High">High</option>
                            </select>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Tasks Table -->
            <Card>
                <CardHeader>
                    <CardTitle>Tasks ({{ tasks.data.length }} of {{ tasks.total }})</CardTitle>
                </CardHeader>
                <CardContent>
                    <div v-if="tasks.data.length > 0" class="space-y-4">
                        <div class="rounder-md border">
                            <table class="w-full caption-bottom text-sm">
                                <thead class="[&_tr]:border-b">
                                    <tr class="border-b transition-colors hover:bg-muted/50">
                                        <th class="h-12 px-4 text-left align-middle font-medium text-muted-foregfround">Task Name</th>
                                        <th class="h-12 px-4 text-left align-middle font-medium text-muted-foregfround">Task Description</th>
                                        <th class="h-12 px-4 text-left align-middle font-medium text-muted-foregfround w-[150px]">List</th>
                                        <th class="h-12 px-4 text-left align-middle font-medium text-muted-foregfround w-[100px]">Priority</th>
                                        <th class="h-12 px-4 text-left align-middle font-medium text-muted-foregfround w-[100px]">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="[&_tr:last-child]:border-0">
                                    <tr v-for="task in tasks.data" :key="task.nTaskNo" class="border-b transition-colors hover:bg-muted/50">
                                        <td class="p-4 align-middle">
                                            <div class="flex items-center gap-3">
                                                <button @click="toggleTaskCompletion(task)" class="flex items-center justify-center shrink-0">
                                                    <CheckCircle2 v-if="task.cCompleted" class="h-5 w-5 text-green-600" />
                                                    <Circle v-else class="h-5 w-5 text-muted-foreground" />
                                                </button>
                                                <span :class="{ 'line-through text-muted-foreground': task.cCompleted }">{{ task.cTaskName }}</span>
                                            </div>
                                        </td>
                                        <td class="p-4 align-middle">
                                            <span class="text-sm text-muted-foreground" :class="{ 'line-through': task.cCompleted }">{{ task.cTasksDescription || '-' }}</span>
                                        </td>
                                        <td class="p-4 align-middle">
                                            <div class="flex items-center gap-2">
                                                <div class="w-3 h-3 rounded-full" :style="{ backgroundColor: task.list.cTaskListsColor || '#ccc' }"></div>
                                                <span class="text-sm">{{ task.list.cTaskListName }}</span>
                                            </div>
                                        </td>
                                        <td class="p-4 align-middle">
                                            <Badge :variant="getPriorityVariant(task.cTaskPriority)">
                                                {{ task.cTaskPriority }}
                                            </Badge>
                                        </td>
                                        <td class="p-4 align-middle">
                                            <div class="flex items-center gap-2">
                                                <Button variant="ghost" size="sm" @click="openEditDialog(task)">
                                                    <Pencil class="h-4 w-4"/>
                                                </Button> 
                                                <Button variant="ghost" size="sm" @click="deleteTask(task.nTaskNo)" :disabled="deletingTaskId === task.nTaskNo">
                                                    <Loader2 v-if="deletingTaskId === task.nTaskNo" class="animate-spin h-4 w-4"/>
                                                    <Trash2 v-else class="h-4 w-4"/>
                                                 </Button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                         <div class="flex items-center justify-between">
                            <p class="text-sm text-muted-foreground">Showing {{ tasks.data.length }} of {{ tasks.total }} tasks</p>
                            <div class="flex items-center gap-2">
                                <Link v-for="link in tasks.links" 
                                    :key="link.label" 
                                    :href="link.url || '#'" 
                                    :class="[
                                        'px-3 py-1 text-sm rounded-md',
                                        link.active
                                        ? 'bg-primary text-primary-foreground'
                                        : link.url
                                        ? 'hover:bg-muted'
                                        : 'opacity-50 cursor-not-allowed'
                                    ]"
                                    :preserve-state="true"
                                    :preserve-scroll="true"
                                    v-html="link.label" />
                            </div>
                         </div>
                    </div>

                    <!-- Empty State -->
                     <div v-else class="text-center py-12 text-muted-foreground">
                        No tasks found.
                     </div>
                </CardContent>
            </Card>
        </div>
</template>