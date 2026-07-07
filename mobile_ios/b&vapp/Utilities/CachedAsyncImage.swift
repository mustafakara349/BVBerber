//
//  CachedAsyncImage.swift
//  b&vapp
//
//  Created by Mustafa KARA on 02.05.2026.
//
//  Harici kütüphane gerektirmeyen, NSCache + URLCache tabanlı görsel önbelleği.
//  API, SwiftUI'nin AsyncImage ile birebir uyumludur.
//
//  Kullanım:
//    CachedAsyncImage(url: URL(string: "https://...")) { phase in
//        switch phase {
//        case .success(let image): image.resizable().scaledToFill()
//        case .failure:            Color.gray
//        case .empty:              ShimmerCard(width: ..., height: ...)
//        @unknown default:        Color.gray
//        }
//    }
//

import SwiftUI
import Combine

// MARK: - In-Memory Image Cache

final class ImageCache {
    static let shared = ImageCache()
    private let cache = NSCache<NSString, UIImage>()

    private init() {
        cache.countLimit = 100   // maksimum 100 görsel
        cache.totalCostLimit = 50 * 1024 * 1024  // 50 MB
    }

    subscript(url: URL) -> UIImage? {
        get { cache.object(forKey: url.absoluteString as NSString) }
        set {
            if let image = newValue {
                cache.setObject(image, forKey: url.absoluteString as NSString,
                                cost: Int(image.size.width * image.size.height * 4))
            } else {
                cache.removeObject(forKey: url.absoluteString as NSString)
            }
        }
    }
}

// MARK: - Async Image Loader

@MainActor
final class ImageLoader: ObservableObject {

    enum State {
        case empty
        case loading
        case success(Image)
        case failure
    }

    @Published var state: State = .empty

    private static let urlSession: URLSession = {
        let config = URLSessionConfiguration.default
        // URLCache: 50 MB bellek + 200 MB disk
        config.urlCache = URLCache(
            memoryCapacity: 50 * 1024 * 1024,
            diskCapacity:   200 * 1024 * 1024
        )
        config.requestCachePolicy = .returnCacheDataElseLoad
        return URLSession(configuration: config)
    }()

    func load(from url: URL) async {
        // 1) In-memory cache'e bak
        if let cached = ImageCache.shared[url] {
            state = .success(Image(uiImage: cached))
            return
        }

        state = .loading

        do {
            let (data, response) = try await Self.urlSession.data(from: url)

            // HTTP hata kontrolü
            if let httpResponse = response as? HTTPURLResponse,
               !(200...299).contains(httpResponse.statusCode) {
                state = .failure
                return
            }

            guard let uiImage = UIImage(data: data) else {
                state = .failure
                return
            }

            // In-memory cache'e ekle
            ImageCache.shared[url] = uiImage
            state = .success(Image(uiImage: uiImage))

        } catch {
            state = .failure
        }
    }
}

// MARK: - CachedAsyncImage View

/// SwiftUI AsyncImage ile aynı API — in-memory + disk cache destekler.
struct CachedAsyncImage<Content: View>: View {

    private let url: URL?
    private let content: (AsyncImagePhase) -> Content

    init(
        url: URL?,
        @ViewBuilder content: @escaping (AsyncImagePhase) -> Content
    ) {
        self.url  = url
        self.content = content
    }

    @StateObject private var loader = ImageLoader()

    var body: some View {
        Group {
            switch loader.state {
            case .empty, .loading:
                content(.empty)
            case .success(let image):
                content(.success(image))
            case .failure:
                content(.failure(URLError(.badURL)))
            }
        }
        .task(id: url?.absoluteString) {
            guard let url else {
                return
            }
            await loader.load(from: url)
        }
    }
}
