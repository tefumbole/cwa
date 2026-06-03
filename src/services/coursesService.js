import { supabase } from "@/lib/customSupabaseClient";

/**
 * Service to manage courses in Supabase.
 * Uses array results (no .single / .maybeSingle) to avoid PGRST116 completely.
 */

// Fetch all courses
export const getCourses = async () => {
  try {
    const { data, error } = await supabase
      .from("courses")
      .select("*")
      .order("name", { ascending: true });

    if (error) throw error;
    return data || [];
  } catch (error) {
    console.error("Error fetching courses:", error);
    throw new Error(error.message || "Could not fetch courses list.");
  }
};

// Fetch a single course by ID
export const getCourse = async (id) => {
  try {
    if (!id) throw new Error("Course ID is required");

    const { data, error } = await supabase
      .from("courses")
      .select("*")
      .eq("id", id)
      .limit(1);

    if (error) throw error;

    const course = data?.[0] ?? null;
    if (!course) {
      throw new Error("Course not found (or access denied).");
    }

    return course;
  } catch (error) {
    console.error("Error fetching course details:", error);
    throw new Error(error.message || "Could not fetch course details.");
  }
};

// Create a new course
export const createCourse = async (courseData) => {
  try {
    if (!courseData?.name || courseData?.price === undefined) {
      throw new Error("Name and Price are required.");
    }

    const payload = {
      ...courseData,
      price: parseFloat(courseData.price),
      created_at: new Date().toISOString(),
      updated_at: new Date().toISOString(),
    };

    const { data, error } = await supabase
      .from("courses")
      .insert([payload])
      .select("*");

    if (error) throw error;

    const created = data?.[0] ?? null;
    if (!created) throw new Error("Course created, but no row returned (check RLS).");

    return created;
  } catch (error) {
    console.error("Error creating course:", error);
    throw new Error(error.message || "Failed to create course.");
  }
};

// Update an existing course (WITH DEBUG LOG)
export const updateCourse = async (id, updates) => {
  try {
    if (!id) throw new Error("Course ID required");

    const payload = {
      ...updates,
      updated_at: new Date().toISOString(),
    };

    // Only parse price if it is actually provided
    if (payload.price !== undefined && payload.price !== null && payload.price !== "") {
      payload.price = parseFloat(payload.price);
    }

    // ✅ DEBUG (this is what you wanted as complete code)
    console.log("updateCourse() id =", id, "payload =", payload);

    const { data, error } = await supabase
      .from("courses")
      .update(payload)
      .eq("id", id)
      .select("*");

    if (error) throw error;

    const updated = data?.[0] ?? null;
    if (!updated) {
      throw new Error("Update failed: course not found (or you don't have permission).");
    }

    return updated;
  } catch (error) {
    console.error("Error updating course:", error);
    throw new Error(error.message || "Failed to update course.");
  }
};

// Delete a course
export const deleteCourse = async (id) => {
  try {
    if (!id) throw new Error("Course ID required");

    const { error } = await supabase
      .from("courses")
      .delete()
      .eq("id", id);

    if (error) throw error;
    return true;
  } catch (error) {
    console.error("Error deleting course:", error);
    throw new Error(error.message || "Failed to delete course.");
  }
};

// Aliases for backward compatibility if needed
export const getAllCourses = getCourses;
export const getCourseById = getCourse;