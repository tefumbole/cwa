import { supabase } from '@/lib/customSupabaseClient';
import { validateImageSize } from '@/utils/imageCompression';

/**
 * Uploads a system logo to Supabase Storage.
 * @param {File} file - The image file to upload
 * @returns {Promise<{publicUrl: string, filePath: string}>} - The public URL and storage path
 */
export const uploadLogo = async (file) => {
  try {
    // 1. Validate File
    const validation = validateImageSize(file);
    if (!validation.valid) {
      throw new Error(validation.error);
    }

    const allowedTypes = ['image/png', 'image/jpeg', 'image/jpg', 'image/webp', 'image/svg+xml'];
    if (!allowedTypes.includes(file.type)) {
      throw new Error("Invalid file type. Only PNG, JPG, JPEG, WebP, and SVG are allowed.");
    }

    // 2. Prepare Upload
    const fileExt = file.name.split('.').pop();
    const fileName = `system-logo-${Date.now()}.${fileExt}`;
    const filePath = `logos/${fileName}`;

    // 3. Upload to Supabase Storage ('system-assets' bucket)
    const { error: uploadError } = await supabase.storage
      .from('system-assets')
      .upload(filePath, file, {
        cacheControl: '3600',
        upsert: false
      });

    if (uploadError) {
      console.error("Storage upload error:", uploadError);
      throw new Error("Failed to upload image. Ensure 'system-assets' bucket exists.");
    }

    // 4. Get Public URL
    const { data: { publicUrl } } = supabase.storage
      .from('system-assets')
      .getPublicUrl(filePath);

    if (!publicUrl) {
      throw new Error("Failed to retrieve public URL for uploaded logo.");
    }

    return { publicUrl, filePath };
  } catch (error) {
    console.error("Logo upload service error:", error);
    throw error;
  }
};

/**
 * Deletes a logo from Supabase Storage.
 * @param {string} filePath - The storage path of the file to delete
 */
export const deleteLogo = async (filePath) => {
    if (!filePath) return;
    
    try {
        const { error } = await supabase.storage
            .from('system-assets')
            .remove([filePath]);
            
        if (error) throw error;
    } catch (error) {
        console.error("Error deleting logo:", error);
        throw error;
    }
};