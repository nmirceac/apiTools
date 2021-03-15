using System;
using System.Dynamic;
using System.IO;


namespace ApiClientTools
{
    public class File
    {
        public static string getMimeTypeFromExtension(string extension, string failoverMime = "application/octet-stream")
        {
            bool identified;
            string fileName, fileMime;
            Microsoft.AspNetCore.StaticFiles.FileExtensionContentTypeProvider extensionContentTypeProvider;

            extensionContentTypeProvider = new Microsoft.AspNetCore.StaticFiles.FileExtensionContentTypeProvider();
            extensionContentTypeProvider.Mappings.Add(".exe", "application/vnd.microsoft.portable-executable");

            
            fileName = "basename."+extension.TrimStart('.');

            identified = (extensionContentTypeProvider.TryGetContentType(fileName, out fileMime));

            if(identified) {
                return fileMime;
            } else {
                return failoverMime;
            }
        }

        public static ExpandoObject getFilePayloadFromPath(string filePath, string role = "files", int order = 0)
        {
            dynamic fileData;
            FileInfo fileInfo;

            if(!System.IO.File.Exists(filePath)) {
                throw new ArgumentException("Couldn't find a file at path", filePath);
            }

            fileData = new ExpandoObject();
            fileInfo = new FileInfo(filePath);

            String filePayload = Convert.ToBase64String(System.IO.File.ReadAllBytes(filePath));
            
            fileData.name = Path.GetFileNameWithoutExtension(filePath);
            fileData.extension = Path.GetExtension(filePath).TrimStart('.');
            fileData.mime = getMimeTypeFromExtension(fileData.extension);

            fileData.basename = fileInfo.Name;
            fileData.size = fileInfo.Length;
            fileData.lastModified = System.IO.File.GetLastWriteTimeUtc(filePath).ToString("yyyy-MM-dd HH:mm:ss");
            fileData.originalPath = Path.GetFullPath(filePath);

            fileData.role = role;
            fileData.order = order;

            fileData.content = filePayload;

            return fileData;
        }
    }
}
