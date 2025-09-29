import pandas as pd
from ortools.sat.python import cp_model
import json
import ast
import sys

original_preferences = ast.literal_eval(sys.argv[1])
supervisors = ast.literal_eval(sys.argv[2])
groups = ast.literal_eval(sys.argv[3])

availability_matrix = pd.DataFrame(original_preferences, index=supervisors, columns=groups)
availability_matrix = availability_matrix.replace(0, 10) # Disfavoured cost

def allocate_supervisors():
    model = cp_model.CpModel()

    # Variables: assign[supervisor][group]
    assign = {}
    for supervisor in supervisors:
        for group in groups:
            assign[(supervisor, group)] = model.NewBoolVar(f'assign_supervisor{supervisor}_group{group}')

    # Calculate bounds
    min_supervisors_per_group = len(supervisors) // len(groups)
    if len(supervisors) % len(groups) == 0:
        max_supervisors_per_group = min_supervisors_per_group
    else:
        max_supervisors_per_group = min_supervisors_per_group + 1
        
    if min_supervisors_per_group == 0:
        min_supervisors_per_group = 1
    
    min_groups_per_supervisor = len(groups) // len(supervisors)
    if len(groups) % len(supervisors) == 0:
        max_groups_per_supervisor = min_groups_per_supervisor
    else:
        max_groups_per_supervisor = min_groups_per_supervisor + 1
    if min_groups_per_supervisor == 0:
        min_groups_per_supervisor = min_groups_per_supervisor + 1

    # Constraint: Range of supervisors assigned to a group
    for group in groups:
        model.Add(sum(assign[supervisor, group] for supervisor in supervisors) >= min_supervisors_per_group)
        model.Add(sum(assign[supervisor, group] for supervisor in supervisors) <= max_supervisors_per_group)

    # Constraint: Range of groups a supervisor can be assigned to
    for supervisor in supervisors:
        model.Add(sum(assign[supervisor, group] for group in groups) >= min_groups_per_supervisor)
        model.Add(sum(assign[supervisor, group] for group in groups) <= max_groups_per_supervisor)

    # Minimise the cost of assignments
    model.Minimize(sum(assign[supervisor, group] * availability_matrix.at[supervisor, group]
                       for supervisor in supervisors for group in groups))

    solver = cp_model.CpSolver()
    solver.parameters.max_time_in_seconds = 20
    status = solver.Solve(model)

    if status in (cp_model.OPTIMAL, cp_model.FEASIBLE):
        assignments = {}
        for group in groups:
            assignments[group] = []
        for supervisor in supervisors:
            for group in groups:
                if solver.Value(assign[supervisor, group]):
                    assignments[group].append(supervisor)
        return assignments
    else:
        return None

assignments = allocate_supervisors()
if assignments:
    json_output = json.dumps(assignments)
    print(json_output)