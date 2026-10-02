<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import {
    Eye,
    Lock,
    Pencil,
    Plus,
    Search,
    Trash2,
    Unlock,
    UserCog,
    UserPlus,
    UserX,
    Check,
} from 'lucide-vue-next';
import { Badge, Button, Card, DataTable, Dialog, Input, Label, Pagination, Select } from '@/components/ui';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { useAuth } from '@/composables/useApp';
import { routes } from '@/lib/routes';
import type { DataTableColumn } from '@/types/ui';
import type { Paginated, User } from '@/types';

defineOptions({ layout: AdminLayout });

const props = defineProps<{
    users: Paginated<User>;
    registrationEnabled: boolean;
    filters: {
        role?: string | null;
        status?: string | null;
        search?: string | null;
        sort?: string | null;
        direction?: string | null;
    };
    title?: string;
    description?: string;
}>();

const { user: currentUser } = useAuth();

/** `all` is a sentinel: reka-ui's Select cannot hold an empty-string value. */
const role = ref(props.filters.role ?? 'all');
const status = ref(props.filters.status ?? 'all');
const search = ref(props.filters.search ?? '');
/** `key:asc|desc` �� matches the DataTable's v-model contract. */
const sort = ref(`${props.filters.sort ?? 'created_at'}:${props.filters.direction === 'asc' ? 'asc' : 'desc'}`);

const roleOptions = [
    { value: 'all', label: 'All roles' },
    { value: 'admin', label: 'Admin' },
    { value: 'lecturer', label: 'Lecturer' },
    { value: 'student', label: 'Student' },
];

const statusOptions = [
    { value: 'all', label: 'All statuses' },
    { value: 'active', label: 'Active' },
    { value: 'inactive', label: 'Inactive' },
];

const isFiltered = computed(
    () => role.value !== 'all' || status.value !== 'all' || search.value.trim() !== '',
);

/** Push the filters to the server; pagination resets because the query changes. */
function applyFilters() {
    const [sortKey, sortDirection] = sort.value.split(':');

    router.get(
        routes.admin.users({
            role: role.value === 'all' ? undefined : role.value,
            status: status.value === 'all' ? undefined : status.value,
            search: search.value.trim() || undefined,
            sort: sortKey,
            direction: sortDirection,
        }),
        {},
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

/** DataTable emits `key:asc|desc`; sorting is done by the database, not the client. */
function onSort(value: string) {
    sort.value = value;
    applyFilters();
}

function clearFilters() {
    role.value = 'all';
    status.value = 'all';
    search.value = '';
    sort.value = 'created_at:desc';
    applyFilters();
}

let searchTimer: ReturnType<typeof setTimeout> | undefined;
watch(search, () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(applyFilters, 350);
});

watch([role, status], applyFilters);

const columns: DataTableColumn[] = [
    { key: 'id', label: 'ID', hideOnMobile: true, align: 'left', sortable: true },
    { key: 'name', label: 'Name', sortable: true },
    { key: 'email', label: 'Email', hideOnMobile: true, sortable: true },
    { key: 'role', label: 'Role', sortable: true },
    { key: 'status', label: 'Status', sortable: true },
    { key: 'created_at', label: 'Joined', hideOnMobile: true, sortable: true },
];

const roleVariant = (value: string) =>
    value === 'admin' ? 'destructive' : value === 'lecturer' ? 'info' : 'success';

const isSelf = (row: User) => row.id === currentUser.value?.id;
const canDelete = (row: User) => !!currentUser.value?.is_super_admin && !isSelf(row);

/** Only your own row is protected; super admins can still be disabled by other admins. */
const canToggle = (row: User) => !isSelf(row);

function toggleStatus(row: User) {
    router.patch(routes.admin.userToggleStatus(row.id), {}, { preserveScroll: true });
}

// --- Delete confirmation ------------------------------------------------
const deleteTarget = ref<User | null>(null);
const deleting = ref(false);

function confirmDelete(row: User) {
    deleteTarget.value = row;
}

function destroy() {
    if (!deleteTarget.value) return;

    deleting.value = true;
    router.delete(routes.admin.userDestroy(deleteTarget.value.id), {
        preserveScroll: true,
        onFinish: () => {
            deleting.value = false;
            deleteTarget.value = null;
        },
    });
}

function toggleRegistration() {
    router.post(routes.admin.toggleRegistration(), {}, { preserveScroll: true });
}

const registrationCopy = computed(() =>
    props.registrationEnabled
        ? 'New users can register on the site.'
        : 'Registration is blocked �? only admins can create accounts.',
);
</script>

<template>
    <!-- Registration toggle + quick actions -->
    <div class="mb-5 grid grid-cols-1 gap-4 lg:grid-cols-12">
        <Card
            class="lg:col-span-5"
            :class="props.registrationEnabled ? 'border-success/40' : 'border-destructive/40'"
        >
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-start gap-3">
                    <span
                        class="flex size-10 shrink-0 items-center justify-center rounded-xl"
                        :class="
                            props.registrationEnabled
                                ? 'bg-success/12 text-success'
                                : 'bg-destructive/12 text-destructive'
                        "
                    >
                        <component :is="props.registrationEnabled ? Unlock : Lock" class="size-5" />
                    </span>
                    <div>
                        <p class="font-bold">Public registration</p>
                        <p class="mt-0.5 text-sm text-muted-foreground">{{ registrationCopy }}</p>
                    </div>
                </div>

                <Button
                    :variant="props.registrationEnabled ? 'destructive' : 'brand'"
                    size="sm"
                    class="shrink-0"
                    @click="toggleRegistration"
                >
                    <component :is="props.registrationEnabled ? UserX : Check" />
                    {{ props.registrationEnabled ? 'Disable' : 'Enable' }}
                </Button>
            </div>
        </Card>

        <div class="flex flex-wrap items-center justify-end gap-2 lg:col-span-7">
            <Button :href="routes.admin.createLecturer()" external variant="outline">
                <UserCog />
                Create lecturer
            </Button>
            <Button :href="routes.admin.userCreate()" variant="brand">
                <UserPlus />
                Add user
            </Button>
        </div>
    </div>

    <!-- Users table -->
    <Card class="min-w-0" :padded="false">
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-bold">All users</h2>
                    <p class="text-sm text-muted-foreground">
                        {{ props.users.total }} {{ props.users.total === 1 ? 'account' : 'accounts' }}
                    </p>
                </div>
            </div>
        </template>

        <div class="min-w-0 px-6 pb-6">
            <!-- Filters -->
            <div class="mb-5 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-12">
                <div class="lg:col-span-3">
                    <Label for="filter-role" class="mb-1.5 block">Role</Label>
                    <Select id="filter-role" v-model="role" :options="roleOptions" />
                </div>
                <div class="lg:col-span-3">
                    <Label for="filter-status" class="mb-1.5 block">Status</Label>
                    <Select id="filter-status" v-model="status" :options="statusOptions" />
                </div>
                <div class="lg:col-span-4">
                    <Label for="filter-search" class="mb-1.5 block">Search</Label>
                    <Input
                        id="filter-search"
                        v-model="search"
                        :icon="Search"
                        placeholder="Name or email�?"
                    />
                </div>
                <div class="flex items-end lg:col-span-2">
                    <Button
                        v-if="isFiltered"
                        variant="ghost"
                        class="w-full"
                        @click="clearFilters"
                    >
                        Clear
                    </Button>
                </div>
            </div>

            <DataTable
                :columns="columns"
                :rows="props.users.data"
                :row-key="(row) => row.id"
                :sort="sort"
                has-actions
                empty-title="No users found"
                empty-description="Try adjusting the filters above."
                @update:sort="onSort"
            >
                <template #cell-id="{ row }">
                    <span class="text-muted-foreground">#{{ row.id }}</span>
                </template>

                <template #cell-name="{ row }">
                    <div class="flex items-center gap-3">
                        <img
                            :src="row.avatar_url"
                            :alt="row.name"
                            class="size-8 shrink-0 rounded-full object-cover ring-1 ring-border"
                            loading="lazy"
                        />
                        <div class="min-w-0">
                            <p class="truncate font-medium">{{ row.name }}</p>
                            <p class="truncate text-xs text-muted-foreground md:hidden">{{ row.email }}</p>
                        </div>
                    </div>
                </template>

                <template #cell-email="{ row }">
                    <span class="text-muted-foreground">{{ row.email }}</span>
                </template>

                <template #cell-role="{ row }">
                    <Badge :variant="roleVariant(row.role)" class="capitalize">{{ row.role }}</Badge>
                </template>

                <template #cell-status="{ row }">
                    <Badge :variant="row.status === 'active' ? 'success' : 'muted'">
                        {{ row.status }}
                    </Badge>
                </template>

                <template #cell-created_at="{ row }">
                    <span class="text-muted-foreground">
                        {{ row.created_at ? new Date(row.created_at).toLocaleDateString() : '�?' }}
                    </span>
                </template>

                <template #actions="{ row }">
                    <Button
                        :href="routes.admin.user(row.id)"
                        external
                        variant="ghost"
                        size="icon-sm"
                        aria-label="View user"
                    >
                        <Eye />
                    </Button>

                    <Button
                        :href="routes.admin.userEdit(row.id)"
                        variant="ghost"
                        size="icon-sm"
                        aria-label="Edit user"
                    >
                        <Pencil />
                    </Button>

                    <Button
                        v-if="canToggle(row)"
                        variant="ghost"
                        size="icon-sm"
                        :aria-label="row.status === 'active' ? 'Disable account' : 'Enable account'"
                        @click="toggleStatus(row)"
                    >
                        <component :is="row.status === 'active' ? UserX : Check" />
                    </Button>

                    <Button
                        v-if="canDelete(row)"
                        variant="ghost"
                        size="icon-sm"
                        class="text-destructive hover:bg-destructive/10 hover:text-destructive"
                        aria-label="Delete user"
                        @click="confirmDelete(row)"
                    >
                        <Trash2 />
                    </Button>
                </template>

                <template #footer>
                    <Pagination
                        :links="props.users.links"
                        :from="props.users.from"
                        :to="props.users.to"
                        :total="props.users.total"
                    />
                </template>
            </DataTable>
        </div>
    </Card>

    <!-- Delete confirmation -->
    <!--
        User uses SoftDeletes, so this sets `deleted_at` — the row is retained and
        can be restored. The copy must not claim the action is permanent.
    -->
    <Dialog
        :open="deleteTarget !== null"
        size="sm"
        title="Delete user"
        :description="`Remove ${deleteTarget?.name ?? ''} from the platform? They will no longer be able to sign in. Their record and history are kept and can be restored.`"
        @update:open="(value: boolean) => !value && (deleteTarget = null)"
    >
        <template #footer>
            <Button variant="outline" :disabled="deleting" @click="deleteTarget = null">Cancel</Button>
            <Button variant="destructive" :loading="deleting" @click="destroy">
                <Trash2 />
                Delete user
            </Button>
        </template>
    </Dialog>
</template>
