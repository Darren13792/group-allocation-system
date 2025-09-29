import pandas as pd
from ortools.sat.python import cp_model

def allocate_students(original_preferences, min_group_size, max_group_size, max_groups_per_topic, ideal_size, students, topics):

    if ideal_size == 'None':
        ideal_size = 0
        deviation_multiplier = 0
    else:
        deviation_multiplier = 10

    preference_matrix = pd.DataFrame(original_preferences, index=students, columns=topics)
    preference_matrix = preference_matrix*10
    preference_matrix = preference_matrix.replace(0, 100) # Disfavoured cost
    model = cp_model.CpModel()

    # Variables: assign[student][topic][group],group_exists[topic][group],size_deviation[topic][group],weighted_deviation[topic][group]
    assign = {}
    group_exists = {}
    size_deviation = {}
    weighted_deviation = {}

    for topic in topics:
        for group in range(max_groups_per_topic):
            group_exists[(topic, group)] = model.NewBoolVar(f'group_exists_topic{topic}_group{group}')
            size_deviation[(topic, group)] = model.NewIntVar(0, max_group_size, f'size_deviation_{topic}_{group}')
            weighted_deviation[(topic, group)] = model.NewIntVar(0, max_group_size * deviation_multiplier, f'weighted_deviation_{topic}_{group}')
            for student in students:
                assign[(student, topic, group)] = model.NewBoolVar(f'assign_student{student}_topic{topic}_group{group}')

    # Constraint: Each student is assigned to only one group
    for student in students:
        model.Add(sum(assign[student, topic, group] for topic in topics for group in range(max_groups_per_topic)) == 1)

    for topic in topics:
        for group in range(max_groups_per_topic):
            # Constraint: Group existence and size limitations
            students_in_group = sum(assign[student, topic, group] for student in students)
            model.Add(students_in_group <= max_group_size).OnlyEnforceIf(group_exists[(topic, group)])
            model.Add(students_in_group >= min_group_size).OnlyEnforceIf(group_exists[(topic, group)])
            model.Add(students_in_group == 0).OnlyEnforceIf(group_exists[(topic, group)].Not())

            # Deviation from ideal group size and corresponding costs
            model.AddAbsEquality(size_deviation[(topic, group)], students_in_group - ideal_size)
            model.Add(weighted_deviation[(topic, group)] == size_deviation[(topic, group)] * deviation_multiplier).OnlyEnforceIf(group_exists[(topic, group)])
            model.Add(weighted_deviation[(topic, group)] == 0).OnlyEnforceIf(group_exists[(topic, group)].Not())

    # Minimise the cost of assignments
    total_preference_cost = sum(assign[student, topic, group] * preference_matrix.at[student, topic] for student in students for topic in topics for group in range(max_groups_per_topic))
    total_deviation_cost = sum(weighted_deviation[topic, group] for topic in topics for group in range(max_groups_per_topic))
    
    model.Minimize(total_preference_cost + total_deviation_cost)

    solver = cp_model.CpSolver()
    solver.parameters.max_time_in_seconds = 20
    status = solver.Solve(model)

    if status == cp_model.OPTIMAL or status == cp_model.FEASIBLE:
        assignments = {}
        for topic in topics:
            topic_groups = []
            for group in range(max_groups_per_topic):
                if solver.Value(group_exists[(topic, group)]):
                    group_list = [student for student in students if solver.Value(assign[student, topic, group])]
                    if group_list:
                        topic_groups.append(group_list)
            if topic_groups:
                assignments[topic] = topic_groups
        return assignments
    else:
        return None

    
