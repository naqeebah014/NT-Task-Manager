 @extends('layouts.app')

@section('content')
    <!-- Login Modal -->
    <div class="modal fade" id="loginModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Login</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="loginForm" method="POST" action="{{ route('login') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Login</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="{{ route('tasks.index') }}">Task Manager</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#" id="dashboardLink">Dashboard</a>
                    </li>
                    @auth
                        @if(auth()->user()->isAdmin())
                            <li class="nav-item">
                                <a class="nav-link" href="#" id="usersLink">Manage Users</a>
                            </li>
                        @endif
                        <li class="nav-item">
                            <a class="nav-link" href="#" id="createTaskLink">Create Task</a>
                        </li>
                    @endauth
                </ul>
                <div class="d-flex">
                    @auth
                        <span class="navbar-text me-3">Welcome, {{ auth()->user()->name }}!</span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger">Logout</button>
                        </form>
                    @else
                        <button class="btn btn-outline-primary" id="loginBtn">Login</button>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mt-4">
        @auth
            <!-- Task Creation Form -->
            <div id="taskForm">
                <h3>Create New Task</h3>
                <form id="createTaskForm" method="POST" action="{{ route('tasks.store') }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="taskTitle" class="form-label">Title</label>
                            <input type="text" class="form-control" id="taskTitle" name="title" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="assignedUser" class="form-label">Assign To</label>
                            <select class="form-control" id="assignedUser" name="assigned_to" required>
                                <option value="">Select User</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="taskDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="taskDescription" name="description" rows="3" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="taskCategory" class="form-label">Category</label>
                            <select class="form-control" id="taskCategory" name="category" required>
                                <option value="development">Development</option>
                                <option value="design">Design</option>
                                <option value="testing">Testing</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="taskPriority" class="form-label">Priority</label>
                            <select class="form-control" id="taskPriority" name="priority" required>
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="taskDeadline" class="form-label">Deadline</label>
                            <input type="date" class="form-control" id="taskDeadline" name="deadline" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Create Task</button>
                </form>
            </div>

            <!-- Task List -->
            <div id="taskList" class="mt-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3>Tasks</h3>
                    <div class="filters">
                        <select class="form-select form-select-sm" id="statusFilter">
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="in-progress">In Progress</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                </div>
                <div class="row" id="taskCards">
                    @foreach($tasks as $task)
                        <div class="col-md-4 mb-4">
                            <div class="task-card card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">{{ $task->title }}</h5>
                                    <span class="priority-badge priority-{{ $task->priority }}">{{ $task->priority }}</span>
                                </div>
                                <div class="card-body">
                                    <p class="card-text">{{ $task->description }}</p>
                                    <div class="task-details">
                                        <p><strong>Category:</strong> {{ $task->category }}</p>
                                        <p><strong>Deadline:</strong> {{ \Carbon\Carbon::parse($task->deadline)->format('m/d/Y') }}</p>
                                        <p><strong>Assigned to:</strong> {{ $task->assignedTo->name }}</p>
                                        <span class="status-badge status-{{ $task->status }}">{{ $task->status }}</span>
                                    </div>
                                    @if(auth()->user()->isAdmin() || auth()->id() === $task->created_by)
                                        <div class="task-actions mt-3">
                                            <button class="btn btn-sm btn-primary" onclick="editTask({{ $task->id }})">Edit</button>
                                            <form action="{{ route('tasks.destroy', $task->id) }}" method="POST" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                            </form>
                                            <select class="form-select form-select-sm mt-2" onchange="updateTaskStatus({{ $task->id }}, this.value)">
                                                <option value="pending" {{ $task->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                                <option value="in-progress" {{ $task->status === 'in-progress' ? 'selected' : '' }}>In Progress</option>
                                                <option value="completed" {{ $task->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                            </select>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- User Management (Admin Only) -->
            @if(auth()->user()->isAdmin())
                <div id="userManagement">
                    <h3>User Management</h3>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="userTable">
                            @foreach($allUsers as $user)
                                <tr>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ ucfirst($user->role) }}</td>
                                    <td>
                                        <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-primary">Edit</a>
                                        <form action="{{ route('users.destroy', $user->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        @else
            <div class="alert alert-info">
                Please login to access the task management system.
            </div>
        @endauth
    </div>
@endsection
