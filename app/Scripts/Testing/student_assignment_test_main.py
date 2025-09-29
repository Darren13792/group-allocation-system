import matplotlib.pyplot as plt
import numpy as np
import time
from student_assignment_test import allocate_students

def generate_preferences(num_students, num_topics, num_preferences):
    data = np.zeros((num_students, num_topics), dtype=int)
    for row in data:
        positions = np.random.choice(num_topics, size=num_preferences, replace=False)
        row[positions] = list(range(1, num_preferences+1))
    return data

def simulate_group_allocation(students, topics_ids, students_ids, min_group_size, max_group_size, max_groups_per_topic):
    
    start_time = time.time()
    result = allocate_students(students, min_group_size, max_group_size, max_groups_per_topic, 'None', students_ids, topics_ids)
    elapsed_time = time.time() - start_time

    preferences = []
    if result:
        for topic, groups in result.items():
            for group in groups:
                for student in group:
                    student_index = students_ids.index(student) # Find student index number
                    topic_preferences = students[student_index] # Use student index number to find student's preferences
                    for i, preference in enumerate(topic_preferences):
                        if preference != 0 and topics_ids[i] == topic: # Check if current preference matches the assigned topic
                            preferences.append(preference)
                            break
    
    return result, elapsed_time, preferences

def visualize_results(num_trials, num_students, num_topics, min_group_size, max_group_size, max_groups_per_topic, test_type, num_preferences):
    all_group_sizes = [[] for _ in range(num_trials)]
    times = []
    all_preferences = []

    students = generate_preferences(num_students, num_topics, num_preferences)
    students_ids = list(range(1, num_students+1))
    topics_ids = list(range(1, num_topics+1))

    for trial in range(num_trials):
        results, time_taken, preferences = simulate_group_allocation(students, topics_ids, students_ids, min_group_size, max_group_size, max_groups_per_topic)
        times.append(time_taken)
        all_preferences.append(preferences)
        if results is not None:
            for topic, groups in results.items():
                for group in groups:
                    all_group_sizes[trial].append(len(group))

    if test_type == 'comparison':
        plt.figure(figsize=(14, 6))
        plt.suptitle('Every group and size')
        for i, group_sizes in enumerate(all_group_sizes):
            plt.subplot(1, num_trials, i+1)
            plt.scatter(range(1, len(group_sizes)+1), group_sizes)
            plt.title(f'Trial {i+1}')
            plt.ylim([min_group_size - 1, max_group_size + 1])
            if i == 0:
                plt.ylabel('Group Size')
            plt.xlabel('Group')
            plt.xticks(range(1, len(group_sizes)+1))
        plt.tight_layout()
        plt.show()

    if test_type == 'time':
        plt.figure(figsize=(7, 5))
        plt.plot(range(1, len(times)+ 1), times, marker='o')
        plt.title('Execution Time for Each Simulation')
        plt.xlabel('Trial')
        plt.ylabel('Time (seconds)')

        plt.tight_layout()
        plt.show()

    if test_type == 'preferences':
        plt.figure(figsize=(14, 6))
        plt.suptitle('Preference choice count')
        for i, preference_counts in enumerate(all_preferences):
            choices = list(range(1, num_preferences+1))
            choices_count = []
            for j in choices:
                choices_count.append(preference_counts.count(j))
            plt.subplot(1, num_trials, i+1)
            plt.bar(choices, choices_count)
            plt.title(f'Trial {i+1}')
            if i == 0:
                plt.ylabel('Number of Students')
            plt.xlabel('Choice')
            plt.xticks(range(1, len(choices_count)+1))
            for i, value in enumerate(choices_count):
                plt.text(i+1, value, value, ha='center', va='bottom')
        plt.tight_layout()
        plt.show()

# Test Types: 'time', 'comparison', 'preferences'
# 'time' displays the time taken for each trial.
# 'comparison' displays the group size for every group, for each trial.
# 'preferences' displays the total count for each preference, for each trial.

# Recommendations: No more than 3 trials for comparison

# Test parameters
test_type = 'comparison'

num_trials = 3
num_students = 100
num_topics = 10
min_group_size = 4
max_group_size = 6
max_groups_per_topic = 5
num_preferences = 2

visualize_results(num_trials, num_students, num_topics, min_group_size, max_group_size, max_groups_per_topic, test_type, num_preferences)