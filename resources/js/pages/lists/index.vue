<script setup lang="ts">
import { Head, Link, Form, router, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import InputError from '@/components/InputError.vue';
import { type BreadcrumbItem } from '@/types';
import { dashboard } from '@/routes';
import { Plus, Pencil, Trash2, ExternalLink, Loader2 } from 'lucide-vue-next';
import { ref } from 'vue';


const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Lists', href: '/lists' },
];

const props = defineProps<{
    lists: {
        nTaskListNo: number;
        cTaskListName: string;
        cTaskListsColor: string;
        created_at: string;
        tasks_count: number;
    }[];
}>();

const isCreatedDialogOpen = ref(false);
const isEditDialogOpen = ref(false);
const editingList = ref<{
    nTaskListNo: number;
    cTaskListName: string;
    cTaskListsColor: string;
} | null>(null);

const deletingListId = ref<number | null>(null);

const createForm = useForm({
    cTaskListName: '',
    cTaskListsColor: '#000000',
});

const editForm = useForm({
    cTaskListName: '',
    cTaskListsColor: '#000000',
});

const openEditDialog = (list: any) => {
    editingList.value = list;
    editForm.cTaskListName = list.cTaskListName;
    editForm.cTaskListsColor = list.cTaskListsColor || '#000000';
    isEditDialogOpen.value = true;
};

const createList = () => {
    createForm.post('/lists', {
        preserveScroll: true,
        onSuccess: () => {
            isCreatedDialogOpen.value = false;
            createForm.reset();
        },
    });
};

const updateList = () => {
    if (!editingList.value) return;

    editForm.put(`/lists/${editingList.value.nTaskListNo}`, {
        preserveScroll: true,
        onSuccess: () => {
            isEditDialogOpen.value = false;
            editForm.reset();
            editingList.value = null;
        },
    });
};

const deleteList = (listId: number) => {
    if (confirm('Are you sure you want to delete this list? All associated tasks will also be deleted.')) {
        deletingListId.value = listId;
        router.delete(`/lists/${listId}`, {
        preserveScroll: true,
        onFinish: () => {
            deletingListId.value = null;
        },
        });
    }
};
</script>

<template>
    <Head title="Lists" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 space-y-6">
            <div class="flex items-center justify-between">
                <h1 class="text-3xl font-bold">Lists</h1>
                <p class="text-muted-foreground">Manage your task lists</p>
            </div>

            <!--Create List Dialog-->
            <Dialog v-model:open="isCreatedDialogOpen">
                <DialogTrigger as-child>
                    <Button>
                        <Plus class="h-4 w-4 mr-2" />
                        Create List
                    </Button>
                </DialogTrigger>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Create new list</DialogTitle>
                        <DialogDescription>
                            Fill in the details to create a new task list and choose color.
                        </DialogDescription>
                    </DialogHeader>
                    <form @submit.prevent="createList" class="space-y-4">
                        <div class="space-y-2">
                            <Label for="cTaskListName">List Name</Label>
                            <Input id="cTaskListName" v-model="createForm.cTaskListName" required placeholder="e.g. Work tasks" />
                            <InputError :message="createForm.errors?.cTaskListName" />
                        </div>
                        <div class="space-y-2">
                            <Label for="cTaskListsColor">List Color</Label>
                            <Input id="cTaskListsColor" v-model="createForm.cTaskListsColor" type="color" />
                            <InputError :message="createForm.errors?.cTaskListsColor" />
                        </div>
                        <Button type="submit" class="w-full" :disabled="createForm.processing">
                            <Loader2 v-if="createForm.processing" class="animate-spin h-4 w-4 mr-2" />
                            {{ createForm.processing ? 'Creating...' : 'Create List' }}
                        </Button>
                    </form>
                </DialogContent>
            </Dialog>

            <!--Edit List Dialog -->
            <Dialog v-model:open="isEditDialogOpen">
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Edit List</DialogTitle>
                        <DialogDescription>
                            Update the list details or color.
                        </DialogDescription>
                    </DialogHeader>
                    <form v-if="editingList" @submit.prevent="updateList" class="space-y-4">
                        <div class="space-y-2">
                            <Label for="edit-cTaskListName">List Name</Label>
                            <Input id="edit-cTaskListName" v-model="editForm.cTaskListName" placeholder="e.g. Work tasks" />
                            <InputError :message="editForm.errors?.cTaskListName" />
                        </div>
                            <div class="space-y-2">
                            <Label for="edit-cTaskListsColor">List Color</Label>
                            <Input id="edit-cTaskListsColor" v-model="editForm.cTaskListsColor" type="color" />
                            <InputError :message="editForm.errors?.cTaskListsColor" />
                        </div>
                        <Button type="submit" class="w-full" :disabled="editForm.processing">
                            <Loader2 v-if="editForm.processing" class="animate-spin h-4 w-4 mr-2" />
                            {{ editForm.processing ? 'Updating...' : 'Update List' }}
                        </Button>
                    </form>
                </DialogContent>
            </Dialog>

        <!-- View Lists -->
        <div v-if="lists.length > 0" class="grid gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
            <Card v-for="list in lists" :key="list.nTaskListNo" class="hover:shadow-md transition-shadow group relative">
                <CardHeader>
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded-full flex-shrink-0" :style="{ backgroundColor: list.cTaskListsColor || '000000' }"></div>
                                <CardTitle class="text-lg">{{ list.cTaskListName }}</CardTitle>
                            <!-- <span class="text-2xl font-bold text-muted-foreground">{{ list.tasks_count || 0 }}</span> -->
                        </div>
                    </div>
                </CardHeader>
                <CardContent>
                    <p class="text-sm text-muted-foreground mb-4">{{ list.tasks_count || 0  }} {{ list.tasks_count === 1 ? 'task' : 'tasks' }}</p>
                    <div class="flex gap-2">
                        <Link :href="`/tasks?list_id=${list.nTaskListNo}`" class="flex-1">
                            <Button variant="outline" size="sm" class="w-full">
                                View Tasks
                                <ExternalLink class="h-4 w-4 ml-2" />
                            </Button>
                        </Link>
                        <Button variant="outline" size="sm" @click="openEditDialog(list)">
                            <Pencil class="h-4 w-4"/>
                        </Button>
                        <Button variant="destructive" size="sm" @click="deleteList(list.nTaskListNo)" :disabled="deletingListId === list.nTaskListNo">
                            <Loader2 v-if="deletingListId === list.nTaskListNo" class="h-4 w-4 animate-spin" />
                            <Trash2 v-else class="h-4 w-4" />
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Empty State -->
        <Card v-else>
            <CardContent class="flex flex-col items-center justify-center py-12">
                <p class="text-muted-foreground mb-4">No lists found.</p>
                <p class="text-sm text-muted-foreground">
                    Get started by creating a new list.
                </p>
            </CardContent>
        </Card>

        </div>
    </AppLayout>
</template>
